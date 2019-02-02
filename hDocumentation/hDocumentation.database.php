<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
# @description
# <h1>Documentation Database API</h1>
# <p>
#   <var>hDocumentationDatabaseLibrary</var> provides several methods that facilitate saving
#   and getting documentation from the database.
# </p>
# @end

class hDocumentationDatabase extends hPlugin {

    public function getMethods($documentationFileId)
    {
        # @return array

        # @description
        # <h2>Getting Methods as an Array</h2>
        # <p>
        #   Returns the methods for the specified <var>$documentationFileId</var> as a
        #   numeric array where each key in the array is a <var>hDocumentationMethodId</var>,
        #   and each value in the array is a corresponding <var>hDocumentationMethodName</var>.
        # </p>
        # @end

       return $this->hDocumentationMethods->selectColumnsAsKeyValue(
            array(
                'hDocumentationMethodId',
                'hDocumentationMethodName'
            ),
            array(
                'hDocumentationFileId' => $documentationFileId
            )
        );
    }

    public function getMethodArguments($methodId)
    {
        # @return array

        # @description
        # <h2>Getting Method Argument as an Array</h2>
        # <p>
        #   Returns the arguments for the specified <var>$methodId</var> as a numeric
        #   array where each key in the array is a <var>hDocumentationMethodArgumentId</var>
        #   and each value in the array is a corresponding <var>hDocumentationMethodArgumentName</var>.
        # </p>
        # @end

        return $this->hDocumentationMethodArguments->selectColumnsAsKeyValue(
            array(
                'hDocumentationMethodArgumentId',
                'hDocumentationMethodArgumentName'
            ),
            array(
                'hDocumentationMethodId' => $methodId
            )
        );
    }

    public function getDocumentationFileId($file, $name)
    {
        # @return integer

        # @description
        # <h2>Getting a Documentation File Id</h2>
        # <p>
        #   Returns a <var>hDocumentationFileId</var> for the supplied
        #   <var>$file</var> (<var>hDocumentationFile</var>, the path to the documentation file).
        #   If the file isn't found, it is created and the supplied <var>$name</var> becomes
        #   <var>hDocumentationFileTitle</var>.
        # </p>
        # @end

        $documentationFileId = $this->hDocumentationFiles->selectColumn(
            'hDocumentationFileId',
            array(
                'hDocumentationFile' => $this->getEndOfPath($file, $this->hFrameworkPath)
            )
        );

        if (empty($documentationFileId))
        {
            $documentationFileId = $this->hDocumentationFiles->insert(
                array(
                    'hDocumentationFile' => $this->getEndOfPath($file, $this->hFrameworkPath),
                    'hDocumentationFileTitle' => $name
                )
            );
        }

        return (int) $documentationFileId;
    }

    public function saveMethods($file, $documentation)
    {
        # @return void

        # @description
        # <h2>Saving Multiple Methods</h2>
        # <p>
        #   Saves methods and documentation extracted from the specified <var>$file</var>.
        # </p>
        # <p>
        #   <var>$documentation</var> is expected to be structured like so:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>description</td>
        #           <td>
        #               A description explaining the purpose of the file, begins at the top of the
        #               file after the copyright notice with the <b>@description</b> opening delimiter
        #               and terminates with the <var>class</var> opening or the <b>@end</b> delimiter,
        #               whichever comes first.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>name</td>
        #           <td>The name of the object the methods belong to</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>methods</td>
        #           <td>
        #               An array of methods found in the object.
        #               See: <a href='#methods'>Methods</a>
        #           </td>
        #       </tr>
        #   </tbody>
        # </table>

        $documentationFileId = $this->getDocumentationFileId(
            $file,
            isset($documentation['name'])? $documentation['name'] : ''
        );

        if ($documentationFileId)
        {
            $methods = $this->getMethods($documentationFileId);

            if (isset($documentation['description']))
            {
                $this->hDocumentationFiles->update(
                    array(
                        'hDocumentationFileTitle' =>
                            isset($documentation['name'])? $documentation['name'] : '',
                        'hDocumentationFileDescription' =>
                            isset($documentation['description'])? hString::escapeAndEncode($documentation['description']) : '',
                        'hDocumentationFileClosingDescription' =>
                            isset($documentation['closingDescription'])? hString::escapeAndEncode($documentation['closingDescription']) : ''
                    ),
                    $documentationFileId
                );
            }

            # <h3 id='methods'>Methods</h3>
            # <p>
            #   The <var>methods</var> array found in the <var>$documentation</var> array
            #   (as <var>$documentation['methods']</var> should be structured like so:
            # </p>
            # <table>
            #   <tbody>
            #       <tr>
            #           <td class='code'>arguments</td>
            #           <td>
            #               An array of arguments used by the method.
            #               See: <a href='#arguments'>Arguments</a>
            #           </td>
            #       </tr>
            #       <tr>
            #           <td class='code'>body</td>
            #           <td>The source code of the method.</td>
            #       </tr>
            #       <tr>
            #           <td class='code'>description</td>
            #           <td>
            #               HTML documentation for the method, typically found inside the method defintion.
            #               Starts with the <b>@description</b> delimiter and terminates with either the <b>@end</b>
            #               delimiter, a different delimiter such as <b>@return</b> or <b>@argument</b> or the end of
            #               the function definition, whichever comes first.  For example:
            #<code># <b>@description</b>
            ## &lt;h2&gt;Some Topcic of Interest&lt;/h2&gt;
            ## &lt;p&gt;
            ##    Blah blah blah.
            ## &lt;/p&gt;
            ## <b>@end</b></code>
            #           </td>
            #       </tr>
            #       <tr>
            #           <td class='code'>isPrivate</td>
            #           <td>Whether or not the method is a <var>private</var> method.</td>
            #       </tr>
            #       <tr>
            #           <td class='code'>isProtected</td>
            #           <td>Whether or not the method is a <var>protected</var> method.</td>
            #       </tr>
            #       <tr>
            #           <td class='code'>isProtected</td>
            #           <td>Whether or not the method is a <var>protected</var> method.</td>
            #       </tr>
            #       <tr>
            #           <td class='code'>isStatic</td>
            #           <td>Whether or not the method is a <var>static</var> method.</td>
            #       </tr>
            #       <tr>
            #           <td class='code'>returnDescription</td>
            #           <td>
            #               HTML documentation describing the return value.  Begins with the <b>@return</b> delimiter
            #               and terminates with either the <b>@end</b>
            #               delimiter, a different delimiter such as <b>@description</b> or <b>@argument</b> or the end of
            #               the function definition, whichever comes first.  For example:
            #<code># <b>@return</b> <i>void</i>
            ## &lt;p&gt;
            ##    Returns the newly inserted unique id.
            ## &lt;/p&gt;
            ## <b>@end</b></code>
            #           </td>
            #       </tr>
            #       <tr>
            #           <td class='code'>returnType</td>
            #           <td>
            #               The type specified in the <b>@return</b> statement.  In the following example:
            #<code># <b>@return</b> <i>void</i>
            ## &lt;p&gt;
            ##    Returns the newly inserted unique id.
            ## &lt;/p&gt;
            ## <b>@end</b></code>
            #               <p>
            #                   The return type is <i>void</i>.
            #               </p>
            #           </td>
            #       </tr>
            #       <tr>
            #           <td class='code'>returnsReference</td>
            #           <td>Whether or not the method returns a variable by reference.</td>
            #       </tr>
            #   </tbody>
            # </table>
            # <p>
            #   In the <var>$documentation['methods']</var> array, each index should be the <var>$methodName</var>.
            # </p>

            foreach ($documentation['methods'] as $methodName => $method)
            {
                if (empty($methodName))
                {
                    continue;
                }

                $methodId = $this->saveMethod(
                    array(
                        'hDocumentationFileId' => $documentationFileId,
                        'hDocumentationMethodName' => $methodName,
                        'hDocumentationMethodSignature' => $method['signature'],
                        'hDocumentationMethodBody' => $method['body'],
                        'hDocumentationMethodDescription' => $method['description'],
                        'hDocumentationMethodIsProtected' => $method['isProtected'],
                        'hDocumentationMethodIsPrivate' => $method['isPrivate'],
                        'hDocumentationMethodIsStatic' => $method['isStatic'],
                        'hDocumentationMethodReturnsReference' => $method['returnsReference'],
                        'hDocumentationMethodReturnType' => $method['returnType'],
                        'hDocumentationMethodReturnDescription' => isset($method['returnDescription'])? $method['returnDescription'] : ''
                    )
                );

                if (isset($method['arguments']))
                {
                    $arguments = $this->getMethodArguments($methodId);

                    # <h3>Arguments</h3>
                    # <p>
                    #   The <var>arguments</var> array found in the <var>$documentation['methods']</var> array
                    #   (as <var>$documentation['methods'][<i>method name</i>]['arguments']</var> should be structured like so:
                    # </p>
                    # <table>
                    #   <tbody>
                    #       <tr>
                    #           <td class='code'>byReference</td>
                    #           <td>
                    #               Whether or not the argument is by reference.  For example:
                    #               <code>public function getFileTitles(&$file)</code>
                    #               <p>
                    #                   In the preceding example, the amphersand preceding the <var>$file</var>
                    #                   argument, or <b>&amp;</b> indicates that the <var>$file</var> argument
                    #                   is passed to <var>getFileTitles()</var> by reference.
                    #               </p>
                    #           </td>
                    #       </tr>
                    #       <tr>
                    #           <td class='code'>default</td>
                    #           <td>
                    #               If the argument is optional, this will be the default value if that
                    #               argument is left out.  For example:
                    #               <code>public function getFileTitles($files = array())</code>
                    #               <p>
                    #                   In the preceding example, <var>array()</var> is the default value
                    #                   of the <var>$files</var> argument.
                    #               </p>
                    #           </td>
                    #       </tr>
                    #       <tr>
                    #           <td class='code'>description</td>
                    #           <td>
                    #               HTML documentation describing the purpose of the argument.
                    #               This starts with the <b>@argument</b> delimiter and terminates with either the <b>@end</b>
                    #               delimiter, a different delimiter such as <b>@description</b> or <b>@return</b> or the end of
                    #               the function definition, whichever comes first.  For example:
                    #<code># <b>@argument</b> $files <i>array</i>
                    ## &lt;p&gt;
                    ##    An array of files to process.
                    ## &lt;/p&gt;
                    ## <b>@end</b></code>
                    #           </td>
                    #       </tr>
                    #       <tr>
                    #           <td class='code'>type</td>
                    #           <td>
                    #               The type of the argument. For example:
                    #<code># <b>@argument</b> $files <i>array</i>
                    ## &lt;p&gt;
                    ##    An array of files to process.
                    ## &lt;/p&gt;
                    ## <b>@end</b></code>
                    #               <p>
                    #                   In the preceding example, <var>array</var> is the type of the argument,
                    #                   and the type is extracted from the <b>@argument</b> statement.
                    #               </p>
                    #           </td>
                    #       </tr>
                    #       <tr>
                    #           <td class='code'>typeHint</td>
                    #           <td>
                    #               If the argument is type hinted, it will have the type hint previous
                    #               to the argument.  For example:
                    #               <code>public function getFileTitles(Array $files = array())</code>
                    #               <p>
                    #                   In the preceding example, <var>Array</var> is the type hint
                    #                   of the <var>$files</var> argument.
                    #               </p>
                    #           </td>
                    #       </tr>
                    #   </tbody>
                    # </table>
                    # <p>
                    #   In the <var>$documentation['methods'][<i>method name</i>]['arguments']</var> array, each
                    #   argument in the array uses the <var>$argumentName</var> as the array key.
                    # </p>

                    $argumentCounter = 0;

                    foreach ($method['arguments'] as $argumentName => $argument)
                    {
                        $argumentId = $this->saveArgument(
                            array(
                                'hDocumentationMethodId' => $methodId,
                                'hDocumentationMethodArgumentIndex' => $argumentCounter,
                                'hDocumentationMethodArgumentName' => $argumentName,
                                'hDocumentationMethodArgumentDescription' => $argument['description'],
                                'hDocumentationMethodArgumentType' => $argument['type'],
                                'hDocumentationMethodArgumentTypeHint' => $argument['typeHint'],
                                'hDocumentationMethodArgumentDefault' => $argument['default'],
                                'hDocumentationMethodArgumentByReference' => $argument['byReference']
                            )
                        );

                        unset($arguments[$argumentId]);

                        $argumentCounter++;
                    }

                    # <h3>Argument Clean-Up</h3>
                    # <p>
                    #   Arguments that are removed in the source code are automatically deleted
                    #   from the documentation.
                    # </p>

                    foreach ($arguments as $argumentId => $argumentName)
                    {
                        $this->deleteArgument($argumentId);
                    }
                }

                unset($methods[$methodId]);
            }

            # <h3>Method Clean-Up</h3>
            # <p>
            #   Methods that are removed from the source code are automatically deleted
            #   from the documentation.
            # </p>

            foreach ($methods as $methodId => $methodName)
            {
                $this->deleteMethodArguments($methodId);
                $this->deleteMethod($methodId);
            }

            # @end
        }
    }

    public function saveMethod(Array $columns)
    {
        # @return integer
        # <p>
        #   The newly inserted <var>hDocumentationMethodId</var> or the <var>hDocumentationMethodId</var> of the
        #   updated method.
        # </p>
        # @end

        # @description
        # <h2>Saving a Method</h2>
        # <p>
        #   Saves an individual method.  Prior to saving, this method checks to see if the
        #   method already exists based on the <var>hDocumentationFileId</var> and the
        #   <var>hDocumentationMethodName</var>.  These two fields are used to retrieve a
        #   <var>$methodId</var>.  If the retrieval of the <var>$methodId</var> is
        #   successful an <var>UPDATE</var> is done, otherwise an <var>INSERT</var> is done.
        # </p>
        # <h3>Required Columns</h3>
        # <p>
        #   The following items need to be passed in the <var>$columns</var> argument.
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hDocumentationFileId</td>
        #           <td>The <var>documentationFileId</var> of the documentation file.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodName</td>
        #           <td>The name of the function.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodSignature</td>
        #           <td>The function's signature.  Example:
        #               <code>public function saveMethod(Array $columns)</code>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodBody</td>
        #           <td>The function's full source code.  Example:
        #               <code>public function deleteMethodArguments($methodId)
        #{
        #   $this-&gt;hDocumentationMethodArguments-&gt;delete('hDocumentationMethodId', $methodId);
        #}</code>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodDescription</td>
        #           <td>
        #               HTML documentation for the method, typically found inside the method defintion.
        #               Starts with the <b>@description</b> delimiter and terminates with either the <b>@end</b>
        #               delimiter, a different delimiter such as <b>@return</b> or <b>@argument</b> or the end of
        #               the function definition, whichever comes first.  For example:
        #<code># <b>@description</b>
        ## &lt;h2&gt;Some Topcic of Interest&lt;/h2&gt;
        ## &lt;p&gt;
        ##    Blah blah blah.
        ## &lt;/p&gt;
        ## <b>@end</b></code>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodIsProtected</td>
        #           <td>Whether or not the method is declared with the <var>protected</var> keyword.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodIsPrivate</td>
        #           <td>Whether or not the method is declared with the <var>private</var> keyword.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodIsStatic</td>
        #           <td>Whether or not the method is declared with the <var>static</var> keyword.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodReturnsReference</td>
        #           <td>Whether or not the method is returns a variable's value by reference.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodReturnType</td>
        #           <td>
        #               The type specified in the <b>@return</b> statement.  In the following example:
        #<code># <b>@return</b> <i>void</i>
        ## &lt;p&gt;
        ##    Returns the newly inserted unique id.
        ## &lt;/p&gt;
        ## <b>@end</b></code>
        #               <p>
        #                   The return type is <i>void</i>.
        #               </p>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodReturnDescription</td>
        #           <td>
        #               HTML documentation describing the return value.  Begins with the <b>@return</b> delimiter
        #               and terminates with either the <b>@end</b>
        #               delimiter, a different delimiter such as <b>@description</b> or <b>@argument</b> or the end of
        #               the function definition, whichever comes first.  For example:
        #<code># <b>@return</b> <i>void</i>
        ## &lt;p&gt;
        ##    Returns the newly inserted unique id.
        ## &lt;/p&gt;
        ## <b>@end</b></code>
        #           </td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $methodId = $this->hDocumentationMethods->selectColumn(
            'hDocumentationMethodId',
            array(
                'hDocumentationFileId' => (int) $columns['hDocumentationFileId'],
                'hDocumentationMethodName' => $columns['hDocumentationMethodName']

            )
        );

        if (empty($methodId))
        {
            $methodId = $this->hDocumentationMethods->insert(
                array(
                    'hDocumentationMethodId' => 0,
                    'hDocumentationFileId' => (int) $columns['hDocumentationFileId'],
                    'hDocumentationMethodName' => $columns['hDocumentationMethodName'],
                    'hDocumentationMethodSignature' => hString::escapeAndEncode($columns['hDocumentationMethodSignature']),
                    'hDocumentationMethodBody' => hString::escapeAndEncode($columns['hDocumentationMethodBody']),
                    'hDocumentationMethodDescription' => hString::escapeAndEncode($columns['hDocumentationMethodDescription']),
                    'hDocumentationMethodIsProtected' => $columns['hDocumentationMethodIsProtected']? 1 : 0,
                    'hDocumentationMethodIsPrivate' => $columns['hDocumentationMethodIsPrivate']? 1 : 0,
                    'hDocumentationMethodIsStatic' => $columns['hDocumentationMethodIsStatic']? 1 : 0,
                    'hDocumentationMethodIsOverloaded' => 0,
                    'hDocumentationMethodReturnsReference' => $columns['hDocumentationMethodReturnsReference']? 1 : 0,
                    'hDocumentationMethodReturnType' => hString::escapeAndEncode($columns['hDocumentationMethodReturnType']),
                    'hDocumentationMethodReturnDescription' =>
                        isset($columns['hDocumentationMethodReturnDescription'])? hString::escapeAndEncode($columns['hDocumentationMethodReturnDescription']) : ''
                )
            );
        }
        else
        {
            $this->hDocumentationMethods->update(
                array(
                    'hDocumentationMethodSignature' => hString::escapeAndEncode($columns['hDocumentationMethodSignature']),
                    'hDocumentationMethodBody' => hString::escapeAndEncode($columns['hDocumentationMethodBody']),
                    'hDocumentationMethodDescription' => hString::escapeAndEncode($columns['hDocumentationMethodDescription']),
                    'hDocumentationMethodIsProtected' => $columns['hDocumentationMethodIsProtected']? 1 : 0,
                    'hDocumentationMethodIsPrivate' => $columns['hDocumentationMethodIsPrivate']? 1 : 0,
                    'hDocumentationMethodIsStatic' => $columns['hDocumentationMethodIsStatic']? 1 : 0,
                    'hDocumentationMethodIsOverloaded' => 0,
                    'hDocumentationMethodReturnsReference' => $columns['hDocumentationMethodReturnsReference']? 1 : 0,
                    'hDocumentationMethodReturnType' => hString::escapeAndEncode($columns['hDocumentationMethodReturnType']),
                    'hDocumentationMethodReturnDescription' =>
                        isset($columns['hDocumentationMethodReturnDescription'])? hString::escapeAndEncode($columns['hDocumentationMethodReturnDescription']) : ''
                ),
                $methodId
            );
        }

        return (int) $methodId;
    }

    public function saveArgument($columns)
    {
        # @return integer
        # <p>
        #   The newly inserted <var>hDocumentationMethodArgumentId</var> or the
        #   <var>hDocumentationMethodArgumentId</var> of the updated method.
        # </p>
        # @end

        # @description
        # <h2>Saving an Argument</h2>
        # <p>
        #   Saves an individual argument.  Prior to saving, this method checks to see if the
        #   argument already exists based on the <var>hDocumentationMethodId</var> and the
        #   <var>hDocumentationMethodArgumentName</var>.  These two fields are used to retrieve an
        #   <var>$argumentId</var>.  If the retrieval of the <var>$argumentId</var> is
        #   successful an <var>UPDATE</var> is done, otherwise an <var>INSERT</var> is done.
        # </p>
        # <h3>Required Columns</h3>
        # <p>
        #   The following items need to be passed in the <var>$columns</var> argument.
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hDocumentationMethodId</td>
        #           <td>The <var>methodId</var> of the method.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodArgumentIndex</td>
        #           <td>The numeric offset position counting from zero of the argument in the argument list.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodArgumentDescription</td>
        #           <td>
        #               HTML documentation describing the purpose of the argument.
        #               This starts with the <b>@argument</b> delimiter and terminates with either the <b>@end</b>
        #               delimiter, a different delimiter such as <b>@description</b> or <b>@return</b> or the end of
        #               the function definition, whichever comes first.  For example:
        #<code># <b>@argument</b> $files <i>array</i>
        ## &lt;p&gt;
        ##    An array of files to process.
        ## &lt;/p&gt;
        ## <b>@end</b></code>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodArgumentType</td>
        #           <td>
        #               The type of the argument. For example:
        #<code># <b>@argument</b> $files <i>array</i>
        ## &lt;p&gt;
        ##    An array of files to process.
        ## &lt;/p&gt;
        ## <b>@end</b></code>
        #               <p>
        #                   In the preceding example, <var>array</var> is the type of the argument,
        #                   and the type is extracted from the <b>@argument</b> statement.  If no
        #                   type is specified, the type hint is used, if there is one.
        #               </p>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodArgumentDefault</td>
        #           <td>
        #               If the argument is optional, this will be the default value if that
        #               argument is left out.  For example:
        #               <code>public function getFileTitles($files = array())</code>
        #               <p>
        #                   In the preceding example, <var>array()</var> is the default value
        #                   of the <var>$files</var> argument.
        #               </p>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationMethodArgumentByReference</td>
        #           <td>
        #               Whether or not the argument is by reference.  For example:
        #               <code>public function getFileTitles(&$file)</code>
        #               <p>
        #                   In the preceding example, the amphersand preceding the <var>$file</var>
        #                   argument, or <b>&amp;</b> indicates that the <var>$file</var> argument
        #                   is passed to <var>getFileTitles()</var> by reference.
        #               </p>
        #           </td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $argumentId = $this->hDocumentationMethodArguments->selectColumn(
            'hDocumentationMethodArgumentId',
            array(
                'hDocumentationMethodId' => $columns['hDocumentationMethodId'],
                'hDocumentationMethodArgumentName' => $columns['hDocumentationMethodArgumentName']
            )
        );

        if (empty($argumentId))
        {
            $argumentId = $this->hDocumentationMethodArguments->insert(
                array(
                    'hDocumentationMethodArgumentId' => 0,
                    'hDocumentationMethodId' => $columns['hDocumentationMethodId'],
                    'hDocumentationMethodArgumentIndex' => $columns['hDocumentationMethodArgumentIndex'],
                    'hDocumentationMethodArgumentName' => $columns['hDocumentationMethodArgumentName'],
                    'hDocumentationMethodArgumentDescription' => hString::escapeAndEncode($columns['hDocumentationMethodArgumentDescription']),
                    'hDocumentationMethodArgumentType' =>
                        empty($columns['hDocumentationMethodArgumentType'])? $columns['hDocumentationMethodArgumentTypeHint'] : $columns['hDocumentationMethodArgumentType'],
                    'hDocumentationMethodArgumentDefault' => hString::escapeAndEncode($columns['hDocumentationMethodArgumentDefault']),
                    'hDocumentationMethodArgumentIsOptional' => !empty($columns['hDocumentationMethodArgumentDefault'])? 1 : 0,
                    'hDocumentationMethodArgumentByReference' => $columns['hDocumentationMethodArgumentByReference']? 1 : 0
                )
            );
        }
        else
        {
            $this->hDocumentationMethodArguments->update(
                array(
                    'hDocumentationMethodArgumentIndex' => $columns['hDocumentationMethodArgumentIndex'],
                    'hDocumentationMethodArgumentDescription' => hString::escapeAndEncode($columns['hDocumentationMethodArgumentDescription']),
                    'hDocumentationMethodArgumentType' =>
                        empty($columns['hDocumentationMethodArgumentType'])? $columns['hDocumentationMethodArgumentTypeHint'] : $columns['hDocumentationMethodArgumentType'],
                    'hDocumentationMethodArgumentDefault' => hString::escapeAndEncode($columns['hDocumentationMethodArgumentDefault']),
                    'hDocumentationMethodArgumentIsOptional' => !empty($columns['hDocumentationMethodArgumentIsOptional'])? 1 : 0,
                    'hDocumentationMethodArgumentByReference' => $columns['hDocumentationMethodArgumentByReference']? 1 : 0
                ),
                $argumentId
            );
        }

        return (int) $argumentId;
    }

    public function deleteMethodArguments($methodId)
    {
        # @return integer
        # <p>
        #   The number of affected rows.
        # </p>
        # @end

        # @description
        # <h2>Deleting Method Arguments</h2>
        # <p>
        #   Deletes all arguments for the specified <var>$methodId</var>.
        # </p>
        # @end

        return $this->hDocumentationMethodArguments->delete(
            'hDocumentationMethodId',
            $methodId
        );
    }

    public function deleteMethod($methodId)
    {
        # @return integer
        # <p>
        #   The number of affected rows.
        # </p>
        # @end

        # @description
        # <h2>Deleting a Method</h2>
        # <p>
        #   Deletes the method and its arguments specified in <var>$methodId</var>.
        # </p>
        # @end

        return $this->hDocumentationMethods->delete(
            'hDocumentationMethodId',
            $methodId
        );
    }

    public function deleteArgument($argumentId)
    {
        # @return integer
        # <p>
        #   The number of affected rows.
        # </p>
        # @end

        # @description
        # <h2>Deleting an Argument</h2>
        # <p>
        #   Deletes the argument specified in <var>$argumentId</var>.
        # </p>
        # @end

        return $this->hDocumentationMethodArguments->delete(
            'hDocumentationMethodArgumentId',
            (int) $argumentId
        );
    }
}

?>