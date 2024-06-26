<?php

namespace TerminoveTrziste\Models;

use TerminoveTrziste\Models\Database\Db;

/**
 * Class for tasks related to subjects
 * @author Jan Štěch
 */
class SubjectManager
{
    public function searchSubjects(string $substring) : array
    {
        $result = Db::fetchQuery(
            "
                SELECT code,title,title_cze,language
                FROM subject
                WHERE (title LIKE CONCAT('%', ?, '%') OR title_cze LIKE CONCAT('%', ?, '%'))
                AND status = 'V';",
            [$substring, $substring],
            true
        );
        if ($result === false) {
            return [];
        }

        $dict = [];
        foreach ($result as $record) {
            if ($record['language'] === 'CZE') {
                $dict[$record['code']] = '🇨🇿 '.$record['title_cze'];
            } else {
                $dict[$record['code']] = '🇬🇧 '.$record['title'];
            }
        }
        return $dict;
    }
}

