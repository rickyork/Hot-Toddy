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

class hMailDatabase extends hPlugin {

    public function hConstructor()
    {

    }

    private function getWhere($mailTemplateId)
    {
        # @return array

        # @description
        # <h2>Get Where For Mail Template</h2>
        # <p>
        #   Sets a WHERE clause for <var>hMailTemplateId</var> or <var>hMailTemplateName</var>.
        # </p>
        # @end

        $where = array();
        $where['hMailTemplate'.(is_numeric($mailTemplateId)? 'Id' : 'Name')] = $mailTemplateId;
        return $where;
    }

    public function templateExists($mailTemplateId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Mail Template Exists</h2>
        # <p>
        #   <var>templateExists()</var> looks in the <var>hMailTemplates</var> table and determines if
        #   a mail template exists in the database.
        # </p>
        # @end

        return $this->hMailTemplates->selectExists('hMailTemplateId', $this->getWhere($mailTemplateId));
    }

    public function getTemplateName($mailTemplateId)
    {
        # @return string

        # @description
        # <h2>Retrieving a Template Id or Name</h2>
        # <p>
        #   Returns the <var>hMailTemplateName</var> for the provided <var>$mailTemplateId</var>.
        # </p>
        # @end

        if (!is_numeric($mailTemplateId))
        {
            return $mailTemplateId;
        }
        else
        {
            return $this->hMailTemplates->selectColumn('hMailTemplateName', $mailTemplateId);
        }
    }

    public function getTemplate($mailTemplateId)
    {
        # @return array

        # @description
        # <h2>Retrieving Template Data</h2>
        # <p>
        #   Retrieves mail template data for the provided <var>$mailTemplateId</var>.
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hMailSubject</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hMailTo</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hMailCc</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hMailBcc</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hMailFrom</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hMailReplyTo</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hMailHTML</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hMailText</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        return $this->hMailTemplates->selectAssociative(
            array(
                'hMailSubject',
                'hMailTo',
                'hMailCc',
                'hMailBcc',
                'hMailFrom',
                'hMailReplyTo',
                'hMailHTML',
                'hMailText'
            ),
            $this->getWhere($mailTemplateId)
        );
    }

    public function templateIsOutdated($mailTemplateId, $mtime)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Mail Template is Outdated</h2>
        # <p>
        #   This method looks at the last modified time of the JSON mail configuration file
        #   stored in the <var>hMailTemplates</var> database table and compares that modified
        #   time with the modified time of the JSON configuration file stored in the file
        #   system.  If the JSON configuration file in the file system has a more recent
        #   modified time than that of the one in the database, the database is automatically
        #   updated to reflect changes in the JSON configuration file.
        # </p>
        # @end

        return ((int) $this->hMailTemplates->selectColumn('hMailJSONLastModified', $this->getWhere($mailTemplateId)) < (int) $mtime);
    }

    public function getTemplateId($mailTemplateName)
    {
        # @return integer

        # @description
        # <h2>Getting a Mail Template Id</h2>
        # <p>
        #   Returns the <var>hMailTemplateId</var> for the specified <var>hMailTemplateName</var>.
        # </p>
        # @end

        return $this->hMailTemplates->selectColumn(
            'hMailTemplateId',
            array(
                'hMailTemplateName' => $mailTemplateName
            )
        );
    }

    public function &saveTemplateFromJSON($templateName, $json, $jsonmtime)
    {
        # @return hMailDatabase

        # @description
        # <h2>Saving a Mail Template from a JSON Configuration File</h2>
        # <p>
        #
        # </p>
        # @end

        if (!$this->templateExists($templateName) || $this->templateIsOutdated($templateName, $jsonmtime))
        {
            $columns = array(
                'hMailTemplateId'   => $this->getTemplateId($templateName),
                'hMailTemplateName' => $templateName
            );

            $mailTemplateColumns = array(
                'hMailTemplateDescription',
                'hMailSubject',
                'hMailTo',
                'hMailCc',
                'hMailBcc',
                'hMailFrom',
                'hMailReplyTo',
                'hMailHTML',
                'hMailText'
            );

            foreach ($json as $key => $value)
            {
                foreach ($mailTemplateColumns as $mailTemplateColumn)
                {
                    if (strtolower($mailTemplateColumn) == strtolower($key) || strtolower(str_replace('hMail', '', $mailTemplateColumn)) == strtolower($key))
                    {
                        $columns[$mailTemplateColumn] = $value;
                    }
                }
            }

            foreach ($mailTemplateColumns as $mailTemplateColumn)
            {
                if (!isset($columns[$mailTemplateColumn]))
                {
                    $columns[$mailTemplateColumn] = '';
                }
            }

            $this->saveTemplate($columns);
        }

        return $this;
    }

    public function &saveTemplate($columns, $directory = null)
    {
        # @return hMailDatabase

        # @description
        # <h2>Saving a Mail Template to the Database</h2>
        # <p>
        #
        # </p>
        # @end

        if (!empty($directory))
        {
            $html = "{$columns['hMailTemplateName']}MailHTML";
            $text = "{$columns['hMailTemplateName']}MailText";

            $htmlPath = $directory.'/'.$columns['hMailTemplateName'].'.mail.html';
            $textPath = $directory.'/'.$columns['hMailTemplateName'].'.mail.txt';

            if (file_exists($htmlPath))
            {
                $columns['hMailHTML'] = file_get_contents($this->$html($htmlPath));
            }

            if (file_exists($textPath))
            {
                $columns['hMailText'] = file_get_contents($this->$text($textPath));
            }
        }

        foreach ($columns as $key => $value)
        {
            $columns[$key] = hString::encodeHTML($value);
        }

        $columns['hMailJSONLastModified'] = time();

        $this->hMailTemplates->save($columns);

        return $this;
    }
}

?>