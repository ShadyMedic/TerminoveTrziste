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
            switch ($record['language']) {
                case 'CZE':
                    $dict[$record['code']] = '🇨🇿 '.$record['title_cze'];
                    break;
                case 'ENG':
                    $dict[$record['code']] = '🇬🇧 '.(empty($record['title']) ? $record['title_cze'] : $record['title']);
                    break;
                case 'GER':
                    $dict[$record['code']] = '🇩🇪 '.(empty($record['title']) ? $record['title_cze'] : $record['title']);
                    break;
                case 'FRE':
                    $dict[$record['code']] = '🇫🇷 '.(empty($record['title']) ? $record['title_cze'] : $record['title']);
                    break;
                case 'SPA':
                    $dict[$record['code']] = '🇪🇸 '.(empty($record['title']) ? $record['title_cze'] : $record['title']);
                    break;
                case 'RUS':
                    $dict[$record['code']] = '🇷🇺 '.(empty($record['title']) ? $record['title_cze'] : $record['title']);
                    break;
                case 'ITA':
                    $dict[$record['code']] = '🇮🇹 '.(empty($record['title']) ? $record['title_cze'] : $record['title']);
                    break;
                default:
                    $dict[$record['code']] = '🏳 '.(empty($record['title']) ? $record['title_cze'] : $record['title']);
            }
        }
        return $dict;
    }
}

