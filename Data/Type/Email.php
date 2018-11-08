<?php
/**
 * This file is part of TEAM.
 *
 * TEAM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, in version 2 of the License.
 *
 * TEAM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TEAM.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Team\Data\Type;

class Email extends Type
{
    private $id = null;
    private $options = [];

    private $to = [];
    private $from = [];
    private $reply = [];
    private $subject = '';
    private $current = [];
    private $status = [];

    /**
     * Inicializamos el envío de correo
     *
     */
    public function initialize($id = 'email', array $options = [])
    {
        $this->id = $id;
        $this->options = $options;
    }

    public function to($email, $name = '')
    {
        $this->addTo($email, $name);
    }

    public function addTo($email, $name = '')
    {
        $this->to[] = ['email' => $email, 'name' => $name];
    }

    public function setReplyTo($email, $name = '')
    {
        $this->replyTo($email, $name);
    }

    public function replyTo($email, $name = '')
    {
        $this->reply = ['email' => $email, 'name' => $name];
    }

    public function send(Array $_data = [], $template = 'team:framework/data/email.tpl')
    {
        $emails = $this->to;
        if (empty($emails)) {
            return false;
        }

        $view = \Team\Config::get('EMAIL_TEMPLATE', $template, $this->id);
        $_data = $_data + $this->data;

        foreach ((array)$emails as $to) {
            $username = $to['name'];
            $useremail = $to['email'];
            //Tenemos que generar el correo electrónico que tendrá
            $this->addCurrent($useremail, $username);

            $email = new \Team\Gui\Template($view, [], $_data);
            $email['EMAIL'] = $_data;
            $email->setContext('ToNAME', $username);
            $email->setContext('ToEMAIL', $useremail);
            $email->setContext('FromNAME', $this->from['name'] ?? '');
            $email->setContext('FromEMAIL', $this->from['email'] ?? '');
            $email->setContext('WEB', \Team\System\Context::get('WEB'));

            $body_html = $email->getHtml();

            $body = wordwrap($body_html, 70);

            $status = mail($this->getTo(), $this->getSubject(), $body, $this->getHeaders());
            $this->status[] = ['name' => $username, 'email' => $useremail, 'status' => $status];
        }

        return new \Team\Db\Collection($this->status);
    }

    private function addCurrent($email, $name = '')
    {
        $this->current = ['email' => $email, 'name' => $name];
    }

    public function getTo()
    {
        return $this->getFormatted($this->current);
    }

    public function setTo($email, $name = '')
    {
        $this->addTo($email, $name);
    }

    private function getFormatted($target)
    {
        if (empty($target)) {
            return '';
        }

        $name = mb_encode_mimeheader($target['name'], "UTF-8", "B");

        return $formatted = "\"$name\" <{$target['email']}>";
    }

    protected function getSubject()
    {
        $subject = $this->subject;
        $subject = str_replace('{$fromname}', $this->from['name'], $subject);
        $subject = str_replace('{$fromemail}', $this->from['email'], $subject);
        $subject = str_replace('{$toname}', $this->current['name'], $subject);
        $subject = str_replace('{$toemail}', $this->current['email'], $subject);

        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    private function getHeaders()
    {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';

        $from = $this->getFromHeader();
        if ($from) {
            $headers[] = $from;
        }

        $replyTo = $this->getReplyToHeader();
        if ($replyTo) {
            $headers[] = $replyTo;
        }

        $headers = \Team\Data\Filter::apply('\team\email\headers', $headers);

        return implode("\r\n", $headers);
    }

    private function getFromHeader()
    {
        return $this->getEmailHeader($this->from, 'From');
    }

    private function getEmailHeader($target, $type)
    {
        $formatted_target = $this->getFormatted($target);

        return $header = $header = "{$type}:  {$formatted_target}";
    }

    private function getReplyToHeader()
    {
        return $this->getEmailHeader($this->reply, 'Reply-To');
    }

    public function getReplyTo()
    {
        return $this->getFormatted($this->reply);
    }

    public function getFrom()
    {
        return $this->getFormatted($this->from);
    }

    public function setFrom($email, $name = '')
    {
        $this->from($email, $name);
    }

    public function from($email, $name = '')
    {
        $this->from = ['email' => $email, 'name' => $name];
    }

    private function getToHeader()
    {
        return $this->getEmailHeader($this->current, 'To');
    }
}