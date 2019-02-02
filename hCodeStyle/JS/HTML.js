// A script for correcting the indentation of HTML documents / fragments

code.style.html = hot.factory();

code.style.html.prototype.init = function(source)
{
    var options = typeof arguments[1] !== 'undefined' ? arguments[1] : {};

    options.lineLength = options.lineLength || 70;
    options.softTabs   = typeof options.softTabs !== 'undefined' ? options.softTabs : true;
    options.tabWidth   = options.tabWidth || 4;
    options.compress   = typeof options.compress !== 'undefined' ? options.compress : false;

    if (isNaN(options.tabWidth))
    {
        options.tabWidth = 4;
    }

    this.hasEncodedEntity = function(source, counter)
    {
        var data = '';

        for (var offset = 0; offset < source.length; offset++)
        {
            var character = source.charAt(counter + offset);

            data += character;

            if (character == ' ' && data.length == 2)
            {
                return false;
            }
            else if (character == ';')
            {
                var matches = data.match(/^\&\#?[A-Z|a-z|0-9]+\;/);

                if (matches && matches.length)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }

        return false;
    };

    this.splitDataIntoLines = function(data)
    {
        var maxCharacterLength = typeof arguments[1] != 'undefined' ? parseInt(arguments[1]) : 70;

        var lastSpaceOffset = 0;
        var spaceCounters = [];

        for (var counter = 0, characterCounter = 0; counter < data.length; counter++)
        {
            var character = data.charAt(counter);

            switch (character)
            {
                case ' ':
                {
                    lastSpaceOffset = counter;
                    break;
                }
            }

            if (characterCounter == maxCharacterLength)
            {
                spaceCounters.push(lastSpaceOffset);

                characterCounter = 0;
            }

            characterCounter++;
        }

        $(spaceCounters).each(
            function(key, spacePosition)
            {
                data = data.substring(0, spacePosition) + "\n" + data.substring(spacePosition + 1);
            }
        );

        return data;
    };

    var isTag = false,
        tag = '',
        isTagType = false,
        tagType = '',
        items = [],
        isData = false,
        data = '';

    // This long nest of booleans isn't used yet, but might be used in the future,
    // if I decide I want more fine-grained control over the markup styling of
    // HTML documents.
    var is = {
        html : false,
        head : false,
        meta : false,
        link : false,
        script : false,
        noscript : false,
        style : false,
        title : false,
        base : false,
        body : false,
        p : false,
        div : false,
        section : false,
        nav : false,
        article : false,
        aside : false,
        address : false,
        main : false,
        span : false,
        a : false,
        b : false,
        i : false,
        u : false,
        strong : false,
        em : false,
        font : false,
        center : false,
        small : false,
        sub : false,
        sup : false,
        ins : false,
        del : false,
        mark : false,
        ruby : false,
        rt : false,
        rp : false,
        bdi : false,
        bdo : false,
        s : false,
        hr : false,
        br : false,
        wbr : false,
        pre : false,
        code : false,
        command : false,
        $var : false,
        blockquote : false,
        cite : false,
        q : false,
        header : false,
        hgroup : false,
        h1 : false,
        h2 : false,
        h3 : false,
        h4 : false,
        h5 : false,
        h6 : false,
        footer : false,
        figure : false,
        figcaption : false,
        ul : false,
        ol : false,
        li : false,
        dl : false,
        dd : false,
        dt : false,
        dfn : false,
        abbr : false,
        data : false,
        time : false,
        block : false,
        inlineBlock : false,
        inline : false,
        table : false,
        colgroup : false,
        col : false,
        thead : false,
        tr : false,
        th : false,
        tbody : false,
        td : false,
        tfoot : false,
        caption : false,
        samp : false,
        kdb : false,
        img : false,
        iframe : false,
        embed : false,
        object : false,
        param : false,
        video : false,
        audio : false,
        source : false,
        track : false,
        canvas : false,
        map : false,
        area : false,
        svg : false,
        math : false,
        fieldset : false,
        legend : false,
        label : false,
        input : false,
        button : false,
        select : false,
        datalist : false,
        optgroup : false,
        option : false,
        textarea : false,
        keygen : false,
        output : false,
        progress : false,
        meter : false,
        details : false,
        summary : false,
        menuitem : false,
        menu : false
    };

    var html = {
        html : {
            type : 'block',
            indentation : 0
        },
        head : {
            type : 'block',
            indentation : 1
        },
        meta : {
            type : 'block',
            indentation : 2
        },
        link : {
            type : 'block',
            indentation : 2
        },
        script : {
            type : 'block',
            indentation : 2
        },
        noscript : {
            type : 'block',
            indentation : 2
        },
        style : {
            type : 'block',
            indentation : 2
        },
        title : {
            type : 'block',
            indentation : 2
        },
        base : {
            type : 'block',
            indentation : 2
        },
        body : {
            type : 'block',
            indentation : 1
        },
        p : {
            type : 'block',
            indentation : 'auto'
        },
        div : {
            type : 'block',
            indentation : 'auto'
        },
        section : {
            type : 'block',
            indentation : 'auto'
        },
        nav : {
            type : 'block',
            indentation : 'auto'
        },
        article : {
            type : 'block',
            indentation : 'auto'
        },
        aside : {
            type : 'block',
            indentation : 'auto'
        },
        address : {
            type : 'inline',
            indentation : 0
        },
        main : {
            type : 'block',
            indentation : 'auto'
        },
        span : {
            type : 'inline',
            indentation : 0
        },
        a : {
            type : 'inline',
            indentation : 0
        },
        b : {
            type : 'inline',
            indentation : 0
        },
        i : {
            type : 'inline',
            indentation : 0
        },
        u : {
            type : 'inline',
            indentation : 0
        },
        strong : {
            type : 'inline',
            indentation : 0
        },
        em : {
            type : 'inline',
            indentation : 0
        },
        font : {
            type : 'inline',
            indentation : 0,
            remove : true
        },
        center : {
            type : 'inline',
            indentation : 0,
            remove : true
        },
        small : {
            type : 'inline',
            indentation : 0
        },
        sub : {
            type : 'inline',
            indentation : 0
        },
        sup : {
            type : 'inline',
            indentation : 0
        },
        ins : {
            type : 'inline',
            indentation : 0
        },
        del : {
            type : 'inline',
            indentation : 0
        },
        mark : {
            type : 'inline',
            indentation : 0
        },
        ruby : {
            type : 'inline',
            indentation : 0
        },
        rt : {
            type : 'inline',
            indentation : 0
        },
        rp : {
            type : 'inline',
            indentation : 0
        },
        bdi : {
            type : 'inline',
            indentation : 0
        },
        bdo : {
            type : 'inline',
            indentation : 0
        },
        s : {
            type : 'inline',
            indentation : 0
        },
        hr : {
            type : 'block',
            indentation : 'auto'
        },
        br : {
            type : 'block',
            indentation : 'auto'
        },
        wbr : {
            type : 'block',
            indentation : 'auto'
        },
        pre : {
            type : 'block',
            indentation : 'auto'
        },
        code : {
            type : 'block',
            indentation : 'auto'
        },
        command : {
            type : 'block',
            indentation : 'auto'
        },
        $var : {
            type : 'inline',
            indentation : 0
        },
        blockquote : {
            type : 'block',
            indentation : 'auto'
        },
        cite : {
            type : 'block',
            indentation : 'auto'
        },
        q : {
            type : 'inline',
            indentation : 0
        },
        header : {
            type : 'block',
            indentation : 'auto'
        },
        hgroup : {
            type : 'block',
            indentation : 'auto'
        },
        h1 : {
            type : 'block',
            indentation : 'auto'
        },
        h2 : {
            type : 'block',
            indentation : 'auto'
        },
        h3 : {
            type : 'block',
            indentation : 'auto'
        },
        h4 : {
            type : 'block',
            indentation : 'auto'
        },
        h5 : {
            type : 'block',
            indentation : 'auto'
        },
        h6 : {
            type : 'block',
            indentation : 'auto'
        },
        footer : {
            type : 'block',
            indentation : 'auto'
        },
        figure : {
            type : 'block',
            indentation : 'auto'
        },
        figcaption : {
            type : 'block',
            indentation : 'auto'
        },
        ul : {
            type : 'block',
            indentation : 'auto'
        },
        ol : {
            type : 'block',
            indentation : 'auto'
        },
        li : {
            type : 'block',
            indentation : 'auto'
        },
        dl : {
            type : 'block',
            indentation : 'auto'
        },
        dd : {
            type : 'block',
            indentation : 'auto'
        },
        dt : {
            type : 'block',
            indentation : 'auto'
        },
        dfn : {
            type : 'block',
            indentation : 'auto'
        },
        abbr : {
            type : 'inline',
            indentation : 0
        },
        data : {
            type : 'inline',
            indentation : 0
        },
        time : {
            type : 'inline',
            indentation : 0
        },
        table : {
            type : 'block',
            indentation : 'auto'
        },
        colgroup : {
            type : 'block',
            indentation : 'auto'
        },
        col : {
            type : 'block',
            indentation : 'auto'
        },
        thead : {
            type : 'block',
            indentation : 'auto'
        },
        tr : {
            type : 'block',
            indentation : 'auto'
        },
        th : {
            type : 'block',
            indentation : 'auto'
        },
        tbody : {
            type : 'block',
            indentation : 'auto'
        },
        td : {
            type : 'block',
            indentation : 'auto'
        },
        tfoot : {
            type : 'block',
            indentation : 'auto'
        },
        caption : {
            type : 'block',
            indentation : 'auto'
        },
        samp : {
            type : 'inline',
            indentation : 0
        },
        kdb : {
            type : 'inline',
            indentation : 0
        },
        img : {
            type : 'inline',
            indentation : 0
        },
        iframe : {
            type : 'block',
            indentation : 'auto'
        },
        embed : {
            type : 'block',
            indentation : 'auto'
        },
        object : {
            type : 'block',
            indentation : 'auto'
        },
        param : {
            type : 'block',
            indentation : 'auto'
        },
        video : {
            type : 'block',
            indentation : 'auto'
        },
        audio : {
            type : 'block',
            indentation : 'auto'
        },
        source : {
            type : 'block',
            indentation : 'auto'
        },
        track : {
            type : 'block',
            indentation : 'auto'
        },
        canvas : {
            type : 'block',
            indentation : 'auto'
        },
        map : {
            type : 'block',
            indentation : 'auto'
        },
        area : {
            type : 'block',
            indentation : 'auto'
        },
        svg : {
            type : 'block',
            indentation : 'auto'
        },
        math : {
            type : 'block',
            indentation : 'auto'
        },
        fieldset : {
            type : 'block',
            indentation : 'auto'
        },
        legend : {
            type : 'block',
            indentation : 'auto'
        },
        label : {
            type : 'block',
            indentation : 'auto'
        },
        input : {
            type : 'block',
            indentation : 'auto'
        },
        button : {
            type : 'block',
            indentation : 'auto'
        },
        select : {
            type : 'block',
            indentation : 'auto'
        },
        datalist : {
            type : 'block',
            indentation : 'auto'
        },
        optgroup : {
            type : 'block',
            indentation : 'auto'
        },
        option : {
            type : 'block',
            indentation : 'auto'
        },
        textarea : {
            type : 'block',
            indentation : 'auto'
        },
        keygen : {
            type : 'block',
            indentation : 'auto'
        },
        output : {
            type : 'block',
            indentation : 'auto'
        },
        progress : {
            type : 'block',
            indentation : 'auto'
        },
        meter : {
            type : 'block',
            indentation : 'auto'
        },
        details : {
            type : 'block',
            indentation : 'auto'
        },
        summary : {
            type : 'block',
            indentation : 'auto'
        },
        menuitem : {
            type : 'block',
            indentation : 'auto'
        },
        menu : {
            type : 'block',
            indentation : 'auto'
        }
    };

    //var characters = source.split('');

    for (var counter = 0; counter < source.length; counter++)
    {
        var character = source.charAt(counter);

        switch (character)
        {
            case '<':
            {
                if (isData)
                {
                    data = $.trim(data);

                    if (data && data.length)
                    {
                        data = data.replace(/\s+/g, ' ');

                        items.push({
                            type : 'text',
                            data : options.compress? data : this.splitDataIntoLines(data)
                        });
                    }

                    isData = false;
                    data = '';
                }

                data = '<';
                isTag = true;
                isTagType = true;

                break;
            }
            case '/':
            {
                if (isTag || isData)
                {
                    data += character;
                }

                break;
            }
            case '>':
            {
                if (isTag)
                {
                    data += '>';

                    data = data.replace(/\s+/g, ' ');

                    items.push({
                        type : tagType,
                        data : data,
                        endTag : data.indexOf('</') != -1
                    });

                    isTag = false;
                    isTagType = false;

                    data = '';
                    tagType = '';
                }

                break;
            }
            case ' ':
            case "\t":
            case "\n":
            case "\r":
            case "\s":
            {
                if (isTagType)
                {
                    isTagType = false;
                }

                if (isData || isTag)
                {
                    data += character;
                }

                break;
            }
            case '&':
            {
                if (!isTag)
                {
                    if (!this.hasEncodedEntity(source, counter))
                    {
                        data += '&amp;';
                    }
                    else
                    {
                        data += character;
                    }
                }
                else
                {
                    data += character;
                }

                break;
            }
            default:
            {
                if (isTag)
                {
                    data += character;
                }

                if (isTagType)
                {
                    tagType += character;
                }

                if (!isTag && !isTagType)
                {
                    isData = true;
                    data += character;
                }
            }
        }
    }

    var source = '';

    var inBlock = false;
    var blockCounter = 0;
    var inlineCounter = 0;

    $(items).each(
        function(key, value)
        {
            if (options.compress)
            {
                source += this.data;
            }
            else
            {
                var indentation = '';
                var applyIndentation = true;
                var addNewLine = true;

                var type = typeof html[this.type] != 'undefined' ? html[this.type].type : 'text';

                var lastItem = typeof items[key - 1] != 'undefined' ? items[key - 1] : {};

                if (typeof lastItem.type != 'undefined')
                {
                    var lastType = typeof html[lastItem.type] != 'undefined' ? html[lastItem.type].type : lastItem.type;
                }
                else
                {
                    var lastType = '';
                }

                var nextItem = typeof items[key + 1] != 'undefined' ? items[key + 1] : {};

                if (typeof nextItem.type != 'undefined')
                {
                    var nextType = typeof html[nextItem.type] != 'undefined' ? html[nextItem.type].type : nextItem.type;
                }
                else
                {
                    var nextType = '';
                }

                var isEndTag = false;

                if (type == 'inline' && !this.endTag)
                {
                    inlineCounter++;
                }

                if (type == 'inline' && this.endTag)
                {
                    inlineCounter--;

                    if (inlineCounter < 0)
                    {
                        inlineCounter = 0;
                    }

                    if (!inlineCounter)
                    {
                        isEndTag = true;
                    }
                }

                if (type == 'inline' && inlineCounter == 1 && !isEndTag)
                {
                    applyIndentation = true;
                    addNewLine = false;
                }
                else if (type == 'text' && inlineCounter == 1 && !isEndTag)
                {
                    applyIndentation = false;
                    addNewLine = false;
                }
                else if (inlineCounter > 1)
                {
                    applyIndentation = false;
                    addNewLine = false;
                }
                else if (isEndTag)
                {
                    applyIndentation = false;
                    addNewLine = true;
                }

                if (applyIndentation)
                {
                    if (this.endTag && type != 'inline')
                    {
                        blockCounter--;

                        if (blockCounter < 0)
                        {
                            blockCounter = 0;
                        }
                    }

                    for (tabCounter = 0; tabCounter < blockCounter; tabCounter++)
                    {
                        if (!options.softTabs)
                        {
                            indentation += "\t";
                        }
                        else
                        {
                            for (spaceCounter = 0; spaceCounter < options.tabWidth; spaceCounter++)
                            {
                                indentation += ' ';
                            }
                        }
                    }
                }

                if (this.type == 'text')
                {
                    var lines = this.data.split("\n");

                    for (var lineCounter = 0; lineCounter < lines.length; lineCounter++)
                    {
                        lines[lineCounter] = (applyIndentation ? indentation : '') + lines[lineCounter];
                    }

                    source += lines.join("\n");
                }
                else
                {
                    source += (applyIndentation ? indentation : '') + this.data;
                }

                if (addNewLine)
                {
                    if (this.type == 'text' || type == 'block' || this.endTag && type == 'inline')
                    {
                        source += "\n";
                    }
                }

                if (typeof html[this.type] != 'undefined' && html[this.type].type == 'block')
                {
                    if (!this.endTag)
                    {
                        blockCounter++;
                    }
                }
            }

            //switch (this.type)
            //{
                // case 'data':
                // {
                //     source += items;
                //     break;
                // }
                // default:
                // {
                //     sources += this.data;
                // }
            //}
        }
    );

    console.log(source);

    this.get = function()
    {
        return source;
    };
};