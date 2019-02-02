<?php
 /**
 * When a hierophant plugin needs to send mail, it goes into a
 * queue, specifically, a database table named mailer_dameon_queue.
 *
 * This lets mail go out in batches, and allows services such as
 * subscription-based forums to function without overwhelming the
 * mail server.
 *
 * The queue is emptied by the amount of specified mail at
 * intervals specified in minutes, both are per-document
 * configurations.
 *
 *  MAILER_DAEMON_BATCH_COUNT
 *  MAILER_DAEMON_INTERVAL
 *
 * This plugin is designed to output results of sending a mail to
 * a terminal/command line, so the HTML template is excluded.
 *
 * This plugin is not intended to be used in/as an HTML document.
 *
 * mailer_dameon_queue
 *
 *  queue_id
 *  headers
 *  body
 *
 * This plugin must be activated via a cron job / scheduled task.
 *
 * Configurations for this daemon must be specified for the
 * daemon plugin.
 *
 */ class hMailDaemon extends hPlugin { public function hConstructor() { echo "Mailer Daemon loaded, processing mail queue.\n\n"; $query = db::query( "SELECT `hMailQueueId`,
                    `hMailMessage`
               FROM `hMailQueue`
              LIMIT 0, ".$this->hMailQueueBatch(10) ); $count = db::numRows($query); if ($count) { $method = $this->hMailSendMethod('sendmail'); for ($c = 1, $s = 0, $f = 0; $data = db::fetchArray($query); $c++) { if (0 !== ($bytes = $this->sendmail(hString::decodeHTML($data['hMailMessage'])))) { echo "Sent {$c} of {$count}... {$bytes} Bytes\n"; db::query( "DELETE
                           FROM `hMailQueue`
                          WHERE `hMailQueueId` = ". $data['hMailQueueId'] ); $s++; } else { echo "{$c} of {$count} failed...\n"; $f++; } } echo "\nSent: {$s}; Failed: {$f}\n"; } else { echo "No mail in queue.\n\n"; } } private function sendmail($mail) { $pipe = popen($this->hMailSendmailPath('/usr/sbin/sendmail -ti').' '.$this->hMailReturnPath, 'w'); $bytes = fputs($pipe, $mail); pclose($pipe); return $bytes; } } ?>