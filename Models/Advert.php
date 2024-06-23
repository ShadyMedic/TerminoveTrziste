<?php

namespace TerminoveTrziste\Models;

use TerminoveTrziste\Models\Database\Db;

/**
 * Model for an advert that can be listed on the board
 * @author Jan Štěch
 */
class Advert
{
    /**
     * @var int ID of this advert in our database
     */
    private int $id;
    /**
     * @var string SIS code of the subject
     */
    private string $subjectCode;
    /**
     * @var string Name of the subject (loaded from SIS by its code)
     */
    private string $subject;
    /**
     * @var string Offered exam date (YYYY-MM-DD format)
     */
    private string $offer;
    /**
     * @var string Exam date wanted in return (YYYY-MM-DD format)
     */
    private string $search;
    /**
     * @var string Deletion token of this advert
     */
    private string $token;
    /**
     * @var string Contact e-mail of this advert's author
     */
    private string $email;
    /**
     * @var bool Whether this advert should be currently listed on the board
     */
    private bool $active = false;
    /**
     * @var bool Whether this advert should be highlighted on the board
     */
    private bool $highlight = false;


    /**
     * Method creating a new database record for this advert and generating values for:
     * - $id
     * - $token (32-character HEX code)
     * @return void
     */
    public function generate()
    {
        $this->id = Db::executeQuery('INSERT INTO advert(`token`) VALUES (?)', [bin2hex(random_bytes(16))], true);
    }

    /**
     * Method loading $subjectCode, $subject and $offer attributes from a link of the webpage in SIS with offered exam
     * date details
     * @param string $sisLink URL leading to the SIS webpage with this exam date's details
     * @return bool True if the URL was parsed successfully and data loaded, FALSE otherwise
     */
    public function loadFromSis(string $sisLink): bool
    {
        $sisId = $this->extractSisId($sisLink);
        if ($sisId === false) {
            return false;
        }
        return $this->loadFromSisId($sisId);
    }

    /**
     * Method loading $subjectCode, $subject and $offer attributes from SIS ID of the offered exam date
     * @param int $examDateSisId SIS ID of the offered exam date
     * @return bool TRUE if the data could be loaded (downloaded), FALSE otherwise
     */
    private function loadFromSisId(int $examDateSisId): bool
    {
        // TODO
    }

    /**
     * Method extracting SIS ID of the exam date from URL of its details webpage
     * @param string $sisLink URL address of an exam date details webpage (example:
     * https://is.cuni.cz/studium/term_st2/index.php?id=1c25fb2e557776ed106&tid=&do=zapsane&sub=detail&ztid=813811 )
     * @return int|bool SIS ID if it could be extracted (813811 from the example above), FALSE otherwise
     */
    private function extractSisId(string $sisLink): int|bool
    {
        $params = array();
        parse_str(parse_url($sisLink, PHP_URL_QUERY), $params);
        return $params['ztid'];
    }

    /**
     * Setter for the $email attribute
     * @param string $email Contact e-mail of this advert's author
     * @return void
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
        Db::executeQuery('UPDATE advert SET email = ? WHERE id = ?', [$email, $this->id]);
    }

    /**
     * Method replicating the current instance for every wanted exam date, keeping the following attributes:
     * - $subjectCode
     * - $subject
     * - $offer
     * - $token
     * - $email
     * - $active
     * - $highlight
     * @param array $dates Array of wanted exam dates (['YYYY-MM-DD','YYYY-MM-DD','YYYY-MM-DD'])
     * @return array Array of all the advert instances bound to the current operation ($this included)
     */
    public function replicateForSearches(array $dates): array
    {
        $this->search = array_shift($dates);
        Db::executeQuery('UPDATE advert SET search = ? WHERE id = ?;', [$this->search, $this->id]);
        $instances = [$this];

        foreach ($dates as $date) {
            $clone = clone $this;
            $clone->id = Db::executeQuery(
                'INSERT INTO advert(subject_code, subject, offer, search, token, email, active, highlight) VALUES (?,?,?,?,?,?,?,?);',
                [$this->subjectCode, $this->subject, $this->offer, $date, $this->token, $this->email, $this->active, $this->highlight],
                true
            );
        }

        return $instances;
    }

    /**
     * Activates this advert (sets the $active attribute to TRUE)
     * @return void
     */
    public function activate()
    {
        $this->active = true;
        Db::executeQuery('UPDATE advert SET active = 1 WHERE id = ?', [$this->id]);
    }

    /**
     * Deactivates this advert (sets the $active attribute to FALSE)
     * @return void
     */
    public function deactivate()
    {
        $this->active = false;
        Db::executeQuery('UPDATE advert SET active = 0 WHERE id = ?', [$this->id]);
    }

    /**
     * Deletes this advert from the database
     * @return void
     */
    public function delete()
    {
        Db::executeQuery('DELETE FROM advert WHERE id = ?', [$this->id]);
    }

    /**
     * Method sending e-mail to this advert's author with a replyTo address and a customized message
     * from the other person wrapped into some preset informational paragraphs
     * This advert will also be deactivated upon successful mail send.
     * @param string $replyTo E-mail address of the person interested in this advert
     * @param string $message Message written by the person interested in this advert
     * @return bool
     */
    public function answer(string $replyTo, string $message) : bool
    {
        $headers = [
            'From' => 'Termínové Tržiště <no-reply@̈́'.$_SERVER['SERVER_NAME'].'>',
            'Content-Type' => 'text/plain; charset=UTF-8'
        ];

        $body =
            "Hi there. Somebody replied to your advert placed on ".$_SERVER['SERVER_NAME']."\n".
            "Their message goes below:\n".
            "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n".
            wordwrap($message, 70, "\n").
            "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n".
            "You can get in touch with the person by sending him an e-mail to:\n".
            "$replyTo\n".
            "\n".
            "Please note that your advert has automatically been deactivated\n".
            "and will be deleted after a few days. If you don't come to an agreement\n".
            "with the person who reacted, you can reactivate your advert by clicking\n".
            "the link below. Please do not reactivate your advert if you exchanged\n".
            "your exam date\n".
            "\n".
            "Reactivate the advert:\n".
            "https://".$_SERVER['SERVER_NAME']."/advert.php?token=".$this->token."\n".
            "\n".
            "Best of luck with your exams!\n".
            "\n".
            "\n".
            "This e-mail has been automatically generated."
        ;

        $result = mail($this->email, 'Somebody is interested in exchanging your exam date for theirs!', $body, implode("\r\n", $headers));
        if ($result) {
            $this->deactivate();
            return true;
        }
        return false;
    }
}

