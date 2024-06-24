<?php

namespace TerminoveTrziste\Models;

use DateTime;
use DOMDocument;
use DOMXPath;
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
     * @var int SIS ID of the offered subject date
     */
    private int $offerSisId;
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
        $this->token = bin2hex(random_bytes(16));
        $this->id = Db::executeQuery('INSERT INTO advert(`token`) VALUES (?)', [$this->token], true);
    }

    /**
     * Method loading $subjectCode, $subject, $offerSisId and $offer attributes from a link of the webpage in SIS with offered exam
     * date details
     * @param string $sisLink URL leading to the SIS webpage with this exam date's details
     * @return array|false Numerical array of subjects that this exam date was created for (containing associative arrays
     *  with keys "Name" and "Code", if everything was set up correctly, FALSE otherwise
     */
    public function loadFromSis(string $sisLink): bool
    {
        //Make sure the link leads to the English version of SIS
        if (!str_contains($sisLink, 'is.cuni.cz/studium/eng/')) {
            $sisLink = str_replace('is.cuni.cz/studium/', 'is.cuni.cz/studium/eng/', $sisLink);
        }

        $sisId = $this->extractSisId($sisLink);
        if ($sisId === false) {
            return false;
        }
        return $this->loadFromSisId($sisId);
    }

    /**
     * Method loading $subjectCode, $subject, $offerSisId and $offer attributes from SIS ID of the offered exam date
     * @param int $examDateSisId SIS ID of the offered exam date
     * @return array|false Numerical array of subjects that this exam date was created for (containing associative arrays
     * with keys "Name" and "Code", if everything was set up correctly, FALSE otherwise
     */
    private function loadFromSisId(int $examDateSisId): array|false
    {
        $html = file_get_contents($examDateSisId);

        if ($html === false) {
            return false;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        //Load general exam date info (we're interested in date and time)
        $examDateDetails = array();
        $select = $xpath->query('//div[@class="form_div"]/table[@class="tab1"]');
        if ($select->count() === 0) {
            return false;
        }
        $table = $select->item(0);
        if (is_null($table)) {
            return false;
        }
        $rows = $table->getElementsByTagName('tr');
        if ($rows->count() === 0) {
            return false;
        }
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            $key = trim(strip_tags($cells->item(0)->nodeValue), " \n\r\t\v\0:");
            $value = strip_tags($cells->item(1)->nodeValue);
            $data[$key] = $value;
            /* The table selected by DOMXPath looks like this:
                <table class="tab1">
                    <tbody>
                        <tr><th>Faculty :</th><td><b>Second Faculty of Medicine</b></td></tr>
                        <tr><th>Guarantor :</th><td><b>Department of Pathology and Molecular Medicine (13-321)</b></td></tr>
                        <tr><th>Date :</th><td><b>Jun 12, 2024 - Wednesday</b></td></tr>
                        <tr><th>Time :</th><td><b>08:00</b></td></tr>
                        <tr><th>End :</th><td><b>Jun 12, 2024, 12:00 AM</b></td></tr>
                        <tr><th>Registration from :</th><td><b>Apr 11, 2024, 07:00 PM</b></td></tr>
                        <tr><th>Cancel before :</th><td><b>Jun 11, 2024, 12:00 PM</b></td></tr>
                        <tr><th>Registration until :</th><td><b>Jun 11, 2024, 12:00 PM</b></td></tr>
                        <tr><th>Capacity :</th><td><b>10</b></td></tr>
                        <tr><th>Number of registered :</th><td><b>5</b></td></tr>
                        <tr><th>Note :</th><td><b>Pouze 1. termíny / Regular 1st examination terms only.</b></td></tr>
                        <tr><th>Information :</th><td>...</td></tr>
                    </tbody>
                </table>
            */
        }

        //Load information about subjects this exam date is made for (we're interested in date and time)
        $subjectsDetails = array();
        $select = $xpath->query('//div[@id="content"]/table[@class="tab1"]')->item(0);
        $rows = $xpath->query('.//tr[@class="row1" or @class="row2"]', $select);
        if ($rows->count() === 0) {
            return false;
        }
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            $code = trim(strip_tags($cells->item(0)->nodeValue));
            $name = trim(strip_tags($cells->item(2)->nodeValue));
            $subjectsDetails[] = array('Code' => $code, 'Name' => $name);

            /* The rows selected by DOMXPath looks like this:
            <tr class="row1"><td><a href="..." class="link3">D0105439</a></td><td>FM2</td><td><a href="..." class="link3">Pathology</a></td><td>2023/2024</td><td>summer</td><td>Ex</td></tr>
            <tr class="row2"><td><a href="..." class="link3">D1105439</a></td><td>FM2</td><td><a href="..." class="link3">Pathology</a></td><td>2023/2024</td><td>summer</td><td>Ex</td></tr>
            */
        }

        $this->offerSisId = $examDateSisId;
        $this->offer = DateTime::createFromFormat('M j, Y;l H:i', $examDateDetails['Date'] . ';' . $examDateDetails['Time'])->format("Y-m-d H:i");

        Db::executeQuery("UPDATE advert SET offer_sis_id = ? AND offer = ? WHERE id = ?;", [$this->offerSisId, $this->offer]);

        return $subjectsDetails;
    }

    /**
     * Method extracting SIS ID of the exam date from URL of its details webpage
     * @param string $sisLink URL address of an exam date details webpage (example:
     * https://is.cuni.cz/studium/eng/term_st2/index.php?id=1c25fb2e557776ed106&tid=&do=zapsane&sub=detail&ztid=813811 )
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
     * Method replicating the current instance for every subject the offered exam date was created for,
     * keeping the following attributes:
     * - $offerSisId
     * - $offer
     * - $token
     * - $email
     * - $active
     * - $highlight
     * @param array $subjects Array of wanted subject records (
     *  [['Name'=>'Subject','Code'=>'D000001],['Name'=>'Subject','Code'=>'D000002],['Name'=>'Subject','Code'=>'D000003])
     * @return array Array of all the advert instances bound to the current operation ($this included)
     */
    public function replicateForSubjects(array $subjects): array
    {
        $firstSubject = array_shift($subjects);
        $this->subject = $firstSubject["Name"];
        $this->subjectCode = $firstSubject["Code"];
        $instances = [$this];

        // TODO
        foreach ($subjects as $subject) {
            $clone = clone $this;
            $clone->id = Db::executeQuery(
                'INSERT INTO advert(subject_code, subject, offer_sis_id, offer, token, email, active, highlight) VALUES (?,?,?,?,?,?,?,?);',
                [$subject["Code"], $subject["Name"], $this->offerSisId, $this->offer, $this->token, $this->email, $this->active, $this->highlight],
                true
            );
        }

        return $instances;
    }

    /**
     * Method replicating the current instance for every wanted exam date, keeping the following attributes:
     * - $subjectCode
     * - $subject
     * - $offerSisId
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
                'INSERT INTO advert(subject_code, subject, offer_sis_id, offer, search, token, email, active, highlight) VALUES (?,?,?,?,?,?,?,?,?);',
                [$this->subjectCode, $this->subject, $this->offerSisId, $this->offer, $date, $this->token, $this->email, $this->active, $this->highlight],
                true
            );
        }

        return $instances;
    }

    public function getSisLink()
    {
        return 'https://is.cuni.cz/studium/term_st2/index.php?do=zapsat&sub=detail&ztid=' . $this->offerSisId;
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
    public function answer(string $replyTo, string $message): bool
    {
        $headers = [
            'From' => 'Termínové Tržiště <no-reply@̈́' . $_SERVER['SERVER_NAME'] . '>',
            'Content-Type' => 'text/plain; charset=UTF-8'
        ];

        $body =
            "Hi there. Somebody replied to your advert placed on " . $_SERVER['SERVER_NAME'] . "\n" .
            "Their message goes below:\n" .
            "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n" .
            wordwrap($message, 70, "\n") .
            "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n" .
            "You can get in touch with the person by sending him an e-mail to:\n" .
            "$replyTo\n" .
            "\n" .
            "Please note that your advert has automatically been deactivated\n" .
            "and will be deleted after a few days. If you don't come to an agreement\n" .
            "with the person who reacted, you can reactivate your advert by clicking\n" .
            "the link below. Please do not reactivate your advert if you exchanged\n" .
            "your exam date\n" .
            "\n" .
            "Reactivate the advert (or delete it instantly):\n" .
            "https://" . $_SERVER['SERVER_NAME'] . "/advert.php?token=" . $this->token . "\n" .
            "\n" .
            "Best of luck with your exams!\n" .
            "\n" .
            "\n" .
            "This e-mail has been automatically generated.";

        $result = mail($this->email, 'Somebody is interested in exchanging your exam date for theirs!', $body, implode("\r\n", $headers));
        if ($result) {
            $this->deactivate();
            return true;
        }
        return false;
    }
}

