<?php

namespace TerminoveTrziste\Models;

use TerminoveTrziste\Models\Database\Db;

/**
 * Model for a manager class that performs operations over multiple adverts
 * @author Jan Štěch
 */
class AdvertManager
{
    /**
     * Class returning list of advert objects, sharing the same token (created together)
     * @param string $token Shared token among all searched adverts
     * @return Advert[]|false Array of advert objects or FALSE if no adverts with this token were found
     */
    public function loadRelatedAdverts(string $token) : array|false
    {
        $result = Db::fetchQuery('SELECT * FROM advert WHERE token = ?;', [$token], true);
        $adverts = [];
        if ($result === false) {
            return false;
        }
        foreach ($result as $record) {
            $adverts[] = new Advert($record);
        }
        return $adverts;
    }

    /**
     * Class returning list of searched dates among all adverts sharing the same token (created together)
     * @param string $token Shared token among all searched adverts
     * @return array Array of wanted exam dates (['YYYY-MM-DD','YYYY-MM-DD','YYYY-MM-DD'])
     */
    public function listSearchesAmongRelated(string $token) : array
    {
        $result = Db::fetchQuery('SELECT DISTINCT search FROM advert WHERE token = ?;', [$token], true);
        return array_column($result, 0);
    }

    /**
     * Method loading all active adverts for a certain subject code
     * @param string $subjectCode Code of subject to filter by
     * @return Advert[]|false Array of found Advert objects
     */
    public function loadAdvertsForSubject(string $subjectCode) : array|false
    {
        $result = Db::fetchQuery('SELECT * FROM advert WHERE subject_code = ? AND active = 1 ORDER BY highlight DESC;', [$subjectCode], true);
        $adverts = [];
        if ($result === false) {
            return false;
        }
        foreach ($result as $record) {
            $adverts[] = new Advert($record);
        }
        return $adverts;
    }

    /**
     * Method loading all active adverts
     * @return Advert[]|false Array of found Advert objects
     */
    public function loadAdverts()
    {
        $result = Db::fetchQuery('SELECT * FROM advert WHERE active = 1 ORDER BY highlight DESC;', [], true);
        $adverts = [];
        if ($result === false) {
            return false;
        }
        foreach ($result as $record) {
            $adverts[] = new Advert($record);
        }
        return $adverts;
    }
}

