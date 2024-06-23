<?php

namespace TerminoveTrziste\Models;

use DateTime;

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
     * @var DateTime Offered exam date
     */
    private DateTime $offer;
    /**
     * @var DateTime Exam date wanted in return
     */
    private DateTime $search;
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
     * - $token
     * @return void
     */
    public function generate()
    {
        // TODO
    }

    /**
     * Method loading $subjectCode, $subject and $offer attributes from a link of the webpage in SIS with offered exam
     * date details
     * @param string $sisLink URL leading to the SIS webpage with this exam date's details
     * @return bool True if the URL was parsed successfully and data loaded, FALSE otherwise
     */
    public function loadFromSis(string $sisLink) : bool
    {
        // TODO
    }

    /**
     * Method loading $subjectCode, $subject and $offer attributes from SIS ID of the offered exam date
     * @param int $examDateSisId SIS ID of the offered exam date
     * @return bool TRUE if the data could be loaded (downloaded), FALSE otherwise
     */
    private function loadFromSisId(int $examDateSisId) : bool
    {
        // TODO
    }

    /**
     * Method extracting SIS ID of the exam date from URL of its details webpage
     * @param string $sisLink URL address of an exam date details webpage
     * @return bool TRUE if the ID could be extracted, False otherwise
     */
    private function extractSisId(string $sisLink) : bool
    {
        // TODO
    }

    /**
     * Setter for the $email attribute
     * @param string $email Contact e-mail of this advert's author
     * @return void
     */
    public function setEmail(string $email)
    {
        // TODO
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
    public function replicateForSearches(array $dates) : array
    {
        // TODO
    }

    /**
     * Activates this advert (sets the $active attribute to TRUE)
     * @return void
     */
    public function activate()
    {
        // TODO
    }

    /**
     * Deactivates this advert (sets the $active attribute to FALSE)
     * @return void
     */
    public function deactivate()
    {
        // TODO
    }

    /**
     * Deletes this advert from the database
     * @return void
     */
    public function delete()
    {
        // TODO
    }

    /**
     * Method sending e-mail to this advert's author with a replyTo address and a customized message
     * from the other person wrapped into some preset informational paragraphs
     * @param string $replyTo E-mail address of the person interested in this advert
     * @param string $message Message written by the person interested in this advert
     * @return void
     */
    public function answer(string $replyTo, string $message)
    {
        //TODO
    }
}

