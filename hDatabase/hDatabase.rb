
#\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#\\\       \\\\\\\\|
#\\\ @@    @@\\\\\\| Hot Toddy Database Plugin
#\\ @@@@  @@@@\\\\\|
#\\\@@@@| @@@@\\\\\|
#\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#\\\\  \\_   \\\\\\|
#\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#\\\\\  ----  \@@@@| http://www.hframework.com/license
#@@@@@\       \@@@@|
#@@@@@@\     \@@@@@|
#\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class HDatabase

#     @@table
#     @@tables = []
#     @@columns
#     @@firstColumns
#     @@primaryKeys
#     @@primaryKeyValue
#     @@primaryIncrementKeys
#     @@useLimit = false
#     @@overloadExceptions = []
#     @@resultCount = 0
#     @@FileIcon
#     @@defaultResult = 0
#     @@prependResult = []
#     @@resultIndex = null
#     @@DB
#     @@lastQuery = null

    def initialize()
        @@methods = ['password', 'find_in_set']

        @@numericTypes = [
            'tinyint',
            'smallint',
            'mediumint',
            'int',
            'bigint',
            'integer',
            'dec',
            'decimal',
            'numeric',
            'float',
            'real'
        ]

        getTables()
    end

    def method_missing(method, *arguments, &block)

        if method[0..5] == 'select'
            case method
                when 'selectColumn'
                    select = 'getColumn'
                when 'selectExists?'    
                    select = 'resultsExist'
                when 'selectAssociative'
                     select = 'getAssociativeResults'
                when 'selectResults'
                     select = 'getResults'
                when 'selectQuery'
                     select = 'query'
                when 'selectCount'
                     select = 'getResultCount'
                when 'selectColumnsAsKeyValue'
                     select = 'getAssociativeArray'
                when 'selectForTemplate'
                      select = 'getResultsForTemplate'
                else
                    $framework.warning "Unimplemented database method \"#{method}\" in #{__FILE__}:#{ __LINE__}"
            end

            return select(
                defined?(*arguments[0]) ? *arguments[0] : '*',   # Columns
                defined?(*arguments[1]) ? *arguments[1] : nil,   # Table(s)
                defined?(*arguments[2]) ? *arguments[2] : nil,   # Where
                defined?(*arguments[3]) ? *arguments[3] : 'AND', # AND, OR
                defined?(*arguments[4]) ? *arguments[4] : nil,   # Order
                defined?(*arguments[5]) ? *arguments[5] : nil,   # Limit
                select
            )
        end

        if tableExists? method
            return select(
                defined?(*arguments[0]) ? *arguments[0] : '*',   # Columns
                method,                                          # Table(s)
                defined?(*arguments[1]) ? *arguments[1] : nil,   # Where
                defined?(*arguments[2]) ? *arguments[2] : 'AND', # AND, OR
                defined?(*arguments[3]) ? *arguments[3] : nil,   # Order
                defined?(*arguments[4]) ? *arguments[4] : nil,   # Limit
                'selectResults'
            )
        end

        if @@DB.empty
            @@DB = Object.const_get('DatabaseDriver_' + $framework.DatabaseDriver('MYSQLI').upcase).new
        end

        if @@DB.respond_to? method
            return @@DB.send(method, *arguments)
        end

        if $framework.respond_to? method
            # Call the method on the framework object.
            return $framework.send(method, *arguments)
        end

        $framework.fuseObjects(method, *arguments)
    end

    def setDefaultResult=(defaultResult)
        @@defaultResult = defaultResult
    end

    def setPrependResult=(value)
        @@prependResult = array[value]
    end

    def setResultIndex=(index)
        @@resultIndex = index
    end

    def setWhere=(where)
        @@where = where
    end

    def singleRecordOperation(key, value = nil, update = false)
        if hasPrimaryKey?
            unless @@primaryKeyValue.empty?
                if update.empty?
                    updateSingle(key, value)
                else
                    return selectSingle(key)
                end
            else
                $framework.warning "There is no primary key value set, unable to select/update a single record in column \"#{key}\" in table \"#{@@table}\" in #{__FILE__}:#{__LINE__}"
            end
        else
            $framework.warning "The table \"#{@@table}\" does not have a primary key defined in #{__FILE__}:#{__LINE__}"
        end
    end

    def columnExists?(column, table = nil)    
        if column =~ /^[A-Z|a-z|0-9|\s|\-|\_|\.]+$/ && table =~ /^[A-Z|a-z|0-9|\s|\-|\_|\.]+$/
            whichTable!(table)
            getColumns(table)

            if !column.empty? && defined?(@@columns[table]) && @@columns[table].kind_of?(Array)
                return @@columns.include? table
            else
                $framework.warning "Unable to determine if the column exists for table \"#{table}\" in #{__FILE__}:#{__LINE__}"
            end
        else
            return false
        end
    end

    def getFilePathSQL
        "REPLACE(CONCAT((SELECT `hDirectoryPath` FROM `hDirectories` WHERE `hDirectoryId` = `hFiles`.`hDirectoryId`), '/', `hFiles`.`hFileName`), '//', '/') AS `hFilePath`"
    end

    def select(columns = '*', tables = nil, where = nil, logicalOperator = 'AND', order = nil, limit = 0, method = 'getResults')

        if !tables.kind_of?(Array) && !where.kind_of?(Array)
            where = whichWhere(where, tables)
            setTable=(tables)
            whichTable!(tables)
        end

        formattedColumns = []
        distinct = false
        count = false

        if columns.kind_of?(Array)
            columns.each do |i, column|
                if column == :SQL_CALC_FOUND_ROWS || column == :COUNT
                    count = true
                    next
                end

                if column == :DISTINCT
                    distinct = true
                    next
                end

                if column == :hFilePath
                    formattedColumns.push(getFilePathSQL())
                    next
                end

                if column.kind_of?(Array)
                    column.each do |n|
                        formattedColumns.push('`' + i.to_str + '`.`' + n.to_str + '`')
                    end
                else
                    unless isNumeric?(i)
                        formattedColumns.push('`' + i.to_str + '`.`' + column.to_str + '`')
                    else
                        formattedColumns.push('`' + column.to_str + '`')
                    end
                end
            end
        else
            case columns
                when '*'

                when :hFilePath
                    columns = getFilePathSQL()
                else
                    columns = "`#{columns}`"
            end

            formattedColumns.push(columns)
        end

        if tables.kind_of?(Array)
            tables.each do |i, table|
                tables[i] = "`#{table}`"
            end
        else
            tables = '`' + tables + '`'
        end

        if where.kind_of?(Array)
            where = where(where, logicalOperator, nil)
        end

        sql = "SELECT "

        if count
            sql << ' SQL_CALC_FOUND_ROWS '
        end
        
        if distinct
            sql << 'DISTINCT '
        end

        sql << formattedColumns.join(', ') + " FROM "

        sql << tables.kind_of?(Array) ? tables.join(', ') : tables

        unless where.empty?
            sql << " WHERE " + where
        end

        unless order.empty?
            sql << " ORDER BY "
            direction = 'ASC'

            random = false

            if order.kind_of?(Array)
                sortByColumns = []

                order.each do |i, column|
                    if column == 'ASC' || column == 'DESC'
                        direction = column
                        next
                    end

                    sortByColumns.push((isNumeric(i) ? '' : "`#{i}`.") + "`#{column}`")
                end

                sql << sortByColumns.join(', ')

            elsif order == 'RAND' || order == 'random'
                sql << " RAND()"
                random = true
            elsif order.include?('.')
                bits = order.split('.')
                sql << "`#{bits[0]}`.`#{bits[1]}`"
            else
                sql << "`#{order}`"
            end

            unless random
                sql << ' ' + direction
            end
        end

        unless limit.empty?
            sql << ' LIMIT ' + limit
        end

        defaultResult = @@defaultResult
        @@defaultResult = 0

        prependResult = @@prependResult
        @@prependResult = []

        resultIndex = @@resultIndex
        @@resultIndex = nil

        @@lastQuery = sql

        case method
            when 'resultsExist', 'getAssociativeResults', 'getResultCount'
                return @@DB.call(method, sql, true)
            when 'getAssociativeArray'
                return getAssociativeArray(sql, defined?(prependResult[0]), defined?(prependResult[0]) ? prependResult[0] : nil)
            when 'getColumn'
                return @@DB.getColumn(sql, defaultResult)
            when 'getResults'
                return getResults(sql, resultIndex)
            else
                return send(method, sql)
        end
    end

    def getLastQuery()
        @@lastQuery
    end

    def selectSingle(column, value = nil, table = nil)

        whichTable!(table)
        whichValue!(value)

        sql = "SELECT `#{column}` FROM `#{table}` WHERE `#{@@primaryKeys[table]}` = '#{value}'"

        @@lastQuery = sql

        getResult(sql)
    end

    def update(*columns, where = nil, table = nil, logicalOperator = 'AND', key = nil, quoteColumns = true)

        whichTable!(table)
        setTable=(table)
        whichWhere!(where, table)

        if where.kind_of?(Array)
            where = where(where, logicalOperator, key)
        end

        unless where.empty?
            columnNames = getColumnsForOperation(*columns, table)

            expandNumericColumns(*columns, columnNames)

            set = []

            *columns.each do |column, value|
                if defined?(@@primaryKeys[table]) && @@primaryKeys[table] != column || !defined?(@@primaryKeys[table])
                    if quoteColumns
                        set.push(getColumnValueSQL(nil, column, '=', value))
                    else
                        set.push("`#{column}` = #{value}")
                    end
                end
            end

            sql = "UPDATE `#{table}` SET " + set.join(',') + " WHERE #{where}" + (@@useLimit ? ' LIMIT 1' : '')

            @@lastQuery = sql

            query(sql)
        end
    end

    def where(columns, logicalOperator = 'AND', key = nil)

        # Get columns from the array
        where = []
        statements = []

        columns.each do |column, value|

            operators = nil
            values = nil
            table = nil
            table2 = nil
            column2 = nil

            if isNumeric?(column)
                column = value
                value  = key
            end

            if column.include?('.')
                table, column = column.split('.')
            end

            unless value.kind_of?(Array)
                columnResult = checkValueForColumns(value)

                if columnResult != false
                    table2, column2 = columnResult
                end

                if column.include?(' ')
                    column, operator = column.split(' ')
                else
                    operator = '='
                end
            else
                if value[0].kind_of?(Array)
                    operators = []
                    values = []

                    value.each do |c, colValue|
                        operators.push(colValue[0])
                        values.push(colValue[1])
                    end
                else
                    operator = value[0]
                    value    = value[1]
                end
            end

            if !operators && !values
                where.push(getColumnValueSQL(table, column, operator, value, table2, column2))
            else
                operators.each do |v, operator|

                    columnResult = checkValueForColumns(values[v])

                    if columnResult != false
                        table2, column2 = columnResult
                    end

                    where.push(getColumnValueSQL(table, column, operator, values[v], table2, column2))
                end          
            end
        end

        where.join(' ' + logicalOperator + ' ')
    end
    
    def checkValueForColumns?(value)
        # The value might be table.column, indicating that the user 
        # wishes to do an implied join
        if value.include?('.')
            # Do a sanity check to rule out values that obviously 
            # are not table.column references.
            if value =~ /^[A-Z|a-z|0-9|\s|\-|\_|\.]+$/
                # Split so that the table and column bits can be validated.
                table, column = value.split('.')

                # The check for the existence of a table is very inexpensive and
                # should rule out the majority of false positives that make it 
                # past the first check
                if tableExists?(table)
                    # Last ditch verification that the portion after the dot, 
                    # is, in fact, a reference to a column.  This will cause 
                    # errors to be logged if the string in question is not a column
                    return columnExists?(column, table) ? [table, column] : false
                else
                    return false
                end
            end
        end
        
        false
    end

    private :checkValueForColumns

    def getColumnValueSQL(table, column, operator, value, table2 = nil, column2 = nil)

        sql = ''

        if value[0..11] == 'FIND_IN_SET('
            # No column and no operator... 
            value = value.gsub(')', ", `#{column}`)")
            operator = false
        end        

        if operator != false
            unless table.empty?
                sql << "`#{table}`."
            end

            sql << "`#{column}` #{operator} "
        end

        if !table2.empty? && !column2.empty?
            sql << "`#{table2}`.`#{column2}`"
        else
            if !isNumeric?(value) && value != 0
                # Some edge cases.. 
                # using a MySQL method on the column value (only password() is supported for now)
                # strings that are already quoted should not be quoted again
                # incrementing a column value
                isMethod = false

                @@methods.each do |method|
                    methodParenthesis = method + '('
                    if value[0..methodParenthesis.length - 2] == methodParenthesis
                        isMethod = true
                        break
                    end
                end

                if value[0..0] == "'" && value[-1..-1] == "'" || isMethod || value[0..0] == "`" && value[-1..-1] == "`"
                    sql << value
                elsif value.include?('+') || value.include?('-') # Increment/Decrement column
                    case true
                        when value.include?('+')
                            operator = '+'
                        when value.include?('-')
                            operator = '-'
                    end

                    first, second = value.split(operator)

                    first.strip!
                    second.strip!

                    sql << (first == column) ? "`#{first}` #{operator} #{second}" : "'#{value}'"
                else
                    sql << "'#{value}'"
                end
            else
                if value[0..0] ==  '0'
                    sql << "'#{value}'"
                else
                    sql << value.empty? ? '0' : value
                end
            end
        end

        sql
    end
    
    private :getColumnValueSQL

    def delete(tables, columns = nil, key = nil, logicalOperator = 'AND')
        @@lastQuery = nil

        if !tables.kind_of?(Array) && !tables.empty? && columns.empty? && key.empty?
            if tableExists?(tables)
                query("TRUNCATE #{tables}")
            else
                $framework.warning "Unable to truncate \"#{tables}\" because it does not exist. in #{__FILE__}:#{__LINE__}"
            end

            return nil
        end

        unless tables.empty?
            unless columns.empty?
                if tables.kind_of?(Array)
                    tables.each do |column, table|

                        if isNumeric?(column)
                            if columns.kind_of?(Array)
                                deleteSingle(where(columns, logicalOperator), table, key)
                            else
                                deleteSingle("`#{columns}` = '#{key}'", table)
                            end
                        else
                            deleteSingle("`#{columns}` = '#{key}'", table)
                        end
                    end
                else
                    if columns.kind_of?(Array)
                        deleteSingle(where(columns, logicalOperator), tables)
                    else
                        deleteSingle("`#{columns}` = '#{key}'", tables)
                    end
                end
            else
                $framework.warning "2nd Argument must be an array of columns, a single column, or a single key value in #{__FILE__}:#{__LINE__}"
            end
        else
            $framework.warning "1st Argument must be an array of tables and columns, an array of tables or a single table in #{__FILE__}:#{__LINE__}"
        end
    end

    def deleteSingle(where = nil, table = nil)
        whichTable!(table)
        whichWhere!(where, table)

        unless where.empty?
            sql = "DELETE FROM `#{table}` WHERE #{where}" + (@@useLimit ? ' LIMIT 1' : '')

            @@lastQuery = sql

            query(sql)
        end
    end

    def updateSingle(column, value, primaryKeyValue = nil, table = nil)
        whichValue!(primaryKeyValue)
        whichTable!(table)

        sql = "UPDATE `#{table}` SET `#{column}` = '#{value}' WHERE `"  + @@primaryKeys[table] + "` = '#{primaryKeyValue}'"

        @@lastQuery = sql

        query(sql)
    end

    def expandNumericColumns(columns, columnNames)
        rtnColumns = []
        rtnColumnNames = []

        columns.each do |column, value|
            # You may pass just the column values to be inserted as 
            # long as the column count of the table matches the 
            # number of values passed.  This automatically 
            # converts the numeric keys into the actual column 
            # names.
            if isNumeric?(column)
                rtnColumns[columnNames[column]] = value
            else
                rtnColumns[column] = value
            end
        end

        rtnColumns, rtnColumnNames
    end

    private :expandNumericColumns

    def insert(columns, table = nil)
        whichTable!(table)
        setTable=(table)

        columnNames = getColumnsForOperation(columns)

        columns, columnNames = expandNumericColumns(columns, columnNames)

        columns.each do |column, value|
            if hasIncrementKey? && @@primaryIncrementKeys[table] == column
                columns[column] = 'null'
            else
                columns[column] = getColumnValueSQL(nil, column, false, value)
            end
        end

        sql = "INSERT INTO `#{table}` (" + $string.implodeToList(columnNames, ',', '`') + ") VALUES (" + columns.join(',') + ")"

        @@lastQuery = sql

        query(sql)

        hasIncrementKey?(table) ? getInsertedId() : 0
    end

    def save(columns, table = nil)
        whichTable!(table)
        setTable=(table)

        # The value of the primary key cannot be null, otherwise
        # it will not survive the filter.  For some reason isset 
        # reports false if the value is set to null.  Instead, 
        # use zero.
        columns = filterTableColumns(columns)

        primaryColumn = getPrimaryKey()

        unless primaryColumn.empty?
            if columns[primaryColumn].empty?
                return insert(columns)
            else
                where = Array[primaryColumn => columns[primaryColumn]]

                if selectExists?(primaryColumn, table, where)
                    update(columns, where)
                    return columns[primaryColumn]
                else
                    return insert(columns)
                end
            end
        else
            firstColumn = getFirstColumn()

            unless firstColumn.empty?
                where = Array[firstColumn => columns[firstColumn]]

                # Take the first column and make a decision to insert based on 
                # whether the value of the first column exists in the table.
                if selectExists?(firstColumn, table, where)
                    update(columns, where)
                else
                    insert(columns)
                end
            else
                $framework.warning "Error retrieving the first column for table \"#{table}\" in #{__FILE__}:#{__LINE__}"
            end
        end
    end

    def getColumnsForOperation(columns, table = nil)
        whichTable!(table)
        setTable=(table)

        if tableExists?(table)
            getColumns(table)
            columnNames = columns.keys

            if !columnsInTable?(columnNames) && defined?(@@columns[table]) && columnNames.length == @@columns[table].length
                columnNames = @@columns[table].keys
            end

            return columnNames
        else
            $framework.warning "Table \"#{table}\" does not exist, unable to get its columns in #{__FILE__}:#{__LINE__}"
        end
    end

    private :getColumnsForOperation

    def filterTableColumns(columns, table = nil)
        whichTable!(table)

        columnsInTable = getColumnNames(table)

        filteredColumns = []

        if columnsInTable.kind_of?(Array) && columnsInTable.length

            columnsInTable.each do |columnInTable|
                if columns.include? columnInTable
                    filteredColumns[columnInTable] = columns[columnInTable]
                end
            end

            unless filteredColumns.length
                if columns.length == columnsInTable.length
                    # Maybe the array passed has numeric indices, see if we can match it to the column count
                    filteredColumns = combineArrays(columnsInTable, columns)
                end
            end

            return filteredColumns
        else
            $framework.warning "Unable to retrieve columns for table \"#{table}\" in #{__FILE__}:#{__LINE__}"
        end
    end

    def columnsInTable?(columns, table = nil)
        whichTable!(table)
        tracker = true

        columns.each do |column|
            unless column.empty?
                unless columnExists?(column, table)
                    tracker = false
                end
            end
        end

        tracker
    end

    def columnInTable?(column, table = nil)
        whichTable!(table)
        getColumns(table)
        @@columns[table].include? column
    end

    def whichTable!(table)
        if table.empty?
            unless @@table.empty?
                table.replace(@@table)
            else
                $framework.warning "No default table is set in #{__FILE__}:#{__LINE__}"
            end
        end

        unless table.empty?
            unless tableExists?(table)
                $framework.warning "Table \"#{table}\" does not exist in the database in #{__FILE__}:#{__LINE__}"
            else
                getColumns(table)
            end
        else
            $framework.warning "No table could be selected in #{__FILE__}:#{__LINE__}"
        end
    end

    private :whichTable

    def setTable=(table, value = nil)
        @@table = table

        unless value.empty?
            whichValue(value)
        def
    end

    def getTables(refresh = false)
        unless defined?(@@tables)
            @@tables = []
        end

        if !@@tables.length || refresh
            query = query("SHOW TABLES FROM `".$framework.DatabaseInitial."`")

            while data = getNumberedResults(query) do
                unless @@tables.include?(data[0])
                    @@tables.push(data[0])
                end
            end
        end

        @@tables
    def

    def tableExists?(table)
        @@tables.include? table
    end

    def whichValue!(value)
        if value.empty?
            unless @@primaryKeyValue.empty?
                value.replace(@@primaryKeyValue)
            else
                $framework.warning "No default primary key value is set in #{__FILE__}:#{__LINE__}"
            end
        end
    end

    def setPrimaryKeyValue=(value)
        @@primaryKeyValue = value
    end

    def hasPrimaryKey?(table = nil)
        whichTable!(table)
        defined?(@@primaryKeys[@@table])
    end

    def isPrimaryKey?(column, table = nil)
        whichTable!(table)

        if hasPrimaryKey?(table) && @@primaryKeys[table] == column
            return true
        end

        false
    end

    def hasIncrementKey?(table = nil)
        whichTable!(table)
        defined?(@@primaryIncrementKeys[table])
    end

    def getPrimaryKey(table = nil)
        whichTable!(table)
        defined?(@@primaryKeys[table]) ? @@primaryKeys[table] : nil
    end

    def getFirstColumn(table = nil)
        whichTable!(table)
        defined?(@@firstColumns[table]) ? @@firstColumns[table] : nil
    end

    def whichWhere!(where, table = nil)
        whichTable!(table)

        if where.empty?
            if @@where.empty?
                if hasPrimaryKey?(table)
                    if @@primaryKeyValue
                        where.replace("`" + @@primaryKeys[table] + "` = '#{primaryKeyValue}'")
                    end
                end
            else
                where.replace(@@where)
            end
        else
            if isNumeric?(where)
                if hasPrimaryKey?(table)
                    where.replace("`" + @@primaryKeys[table] + "` = " + where)
                else
                    firstColumn = getFirstColumn(table)

                    # Make the statement start with the first column of the table... 
                    unless firstColumn.empty?
                        where.replace("`#{firstColumn}` = " + where)
                    else
                        $framework.warning "Unable to determine a column to use for table \"#{table}\" with value \"#{where}\" in #{__FILE__}:#{__LINE__}"
                    end
                end
            end
        end

        where
    end

    def decideResultCount(sql)
        if sql.kind_of?(String)
            @@countType =  (sql.include?('SQL_CALC_FOUND_ROWS') ? '' : 'numRows'
        end
    end
    
    private :decideResultCount
    
    def setResultCount(query = nil)
        @@resultCount = @@countType ? resultsExist(query) : getColumn("SELECT FOUND_ROWS()")
    end

    private :setResultCount

    def getResultCount()
        @@resultCount
    end

    def getAssociativeArray(sql, prependValue = false, prependString = '')
        decideResultCount(sql)
        query = query(sql)
        setResultCount(query)

        options = []

        unless prependValue.empty?
            options.push(prependString)
        end

        while data = getNumberedResults(query) do
            options[data[0]] = data[1]
        end
        
        closeResults(query)

        options
    end

    def getResultsAsArray(sql, index = nil)
        getResults(sql, index)
    end

    def getResults(sql, index = nil, *arguments)
        decideResultCount(sql)

        if sql.kind_of?(String)
            query = query(sql)
        else
            $framework.warning(
                "Unable to get database results because sql was not a string. " +
                "You probably meant to call select(). Now attempting to " +
                "automatically reconcile this problem in #{__FILE__}:#{__LINE__}"
            )

            if sql.kind_of?(Array)
                send(:select, sql, index, *arguments)
            end

            return sql
        end

        setResultCount(query)

        results = []

        unless index.empty?
            while data = getAssociativeResults(query) do
               results[defined?(data[index]) ? data[index] : results.length] = data
            end

            closeResults(query)

            return results
        else

            while data = getArrayResults(query) do

                if defined?(data[0]) && !defined?(data[1])
                    results.push(data[0])
                else
                    filteredData = []

                    data.each do |key, value|
                        unless isNumeric?(key)
                            filteredData[key] = value
                        end
                    end

                    results.push(filteredData)
                end
            end

            closeResults(query)
        end

        results
    end

    def getResultsForTemplate(results)

        if results.kind_of?(Array)
            rtn = []

            results.each do |i, result|
                if result.kind_of?(Array)
                    unless defined?(rtn[key])
                        rtn[key] = []
                    end

                    result.each do |key, value|
                        rtn[key].push(value)
                    end
                else
                    $framework.warning "Get results for template failed!  Item \"#{result}\" is not an array in #{__FILE__}:#{ __LINE__}"
                end
            end
            
            if defined?(key) && defined?(rtn[key])
                i = rtn[key].length

                if i
                    c = 0

                    rtn[key].each do |key, value|
                        rtn[:isOdd].push(!c & 1)
                        c++
                    end
                end
            end

            if defined?(rtn[:hFileId]) && $framework.FileGetMetaData
                return $framework.getFileMetaDataForTemplate(rtn)
            end

            return rtn
        else
            return getResultsForTemplate(getResults(results))
        end
    end

    def implodeResults(sql, glue = '')
        query = query(sql)

        rtn = []

        while data = getAssociativeResults(query) do
            data.each do |value|
                rtn.push(value)
            end
        end

        closeResults(query)

        rtn.join(glue)
    end

    def getResult(sql, default = '')
        getColumn(sql, default)
    end

    def getResultByKey(field, table, key, keyValue, default = '')
        getColumn("SELECT `#{field}` FROM `#{table}` WHERE `#{key}` = " + keyValue.to_int, default)
    end

    def getColumnType(column, table = nil)
        whichTable!(table)
        getColumns(table)

        if defined?(@@columns[table][column]['Type'])
            return @@columns[table][column]['Type']    
        else
            $framework.warning "No type is defined for column \"#{column}\" in table \"#{table}\" in #{__FILE__}:#{__LINE__}"
        end
    end

    def columnIsNumeric?(column, table = nil)
        whichTable!(table)
        return @@numericTypes.include? getColumnType(column, table).split('(').shift
    end

    def getColumns(table)
        table.strip!

        if table.empty?
            $framework.warning "Unable to get columns for table because no table was provided in #{__FILE__}:#{__LINE__}"
        else
            if !defined?(@@columns[table]) || @@columns[table].empty? || !@@columns[table].kind_of?(Array) || !@@columns[table].length
                query = getResults("SHOW COLUMNS FROM `#{table}`")

                if query.length
                    i = 0

                    query.each do |data|
                        unless i
                            @@firstColumns[table] = data[:Field]
                        end

                        @@columns[table][data[:Field]] = data
    
                        if data[:Key] == 'PRI'
                            @@primaryKeys[table] = data[:Field]
                        end
    
                        if data[:Extra] == 'auto_increment'
                            @@primaryIncrementKeys[table] = data[:Field]
                        end

                        i++
                    end
                else
                    $framework.warning "Query Failed:\n #{query}\n\nIn #{__FILE__}:#{__LINE__}"
                end
            end
        end
    end

    def uses(*arguments)
        *arguments.each do |table|
            createTableFromFile(table)
        end
    end

    def createTableFromFile(table)
        unless tableExists?(table)
            path = $framework.PluginsPath + '/hDatabase/hDatabaseStructure/' + table + '/' + table + '.sql'

            if File.exists?(path)
                # Attempt to automatically create each database table.
                query(getFileContents(path))
            end
        end
    end

    def getPostDataByColumnName(table, columns = Array[])
        columnsInTable = getColumnNames(table)

        data = []

        columnsInTable.each do |column|
            if defined?($request[column]) && (columns.length && columns.include? column || !columns.length)
                data[column] = $request[column]
            end
        end

        data
    end

    def getColumnNames(table)
        unless defined?(@@columns[table])
            getColumns(table)
        end

        @@columns[table].keys
    end
end