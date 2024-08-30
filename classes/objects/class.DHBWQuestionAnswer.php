<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace objects;

use platform\DHBWTrainingDatabase;

/**
 * Class DHBWQuestionAnswer
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWQuestionAnswer {
    private int $answer_id;
    private int $question_id;
    private int $a_order;
    private string $answertext;
    private int $cloze_type;
    private float $points;

    public function getAnswerId(): int
    {
        return $this->answer_id;
    }

    public function setAnswerId(int $answer_id): void
    {
        $this->answer_id = $answer_id;
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function setQuestionId(int $question_id): void
    {
        $this->question_id = $question_id;
    }

    public function getAOrder(): int
    {
        return $this->a_order;
    }

    public function setAOrder(int $a_order): void
    {
        $this->a_order = $a_order;
    }

    public function getAnswertext(): string
    {
        return $this->answertext;
    }

    public function setAnswertext(string $answertext): void
    {
        $this->answertext = $answertext;
    }

    public function getClozeType(): int
    {
        return $this->cloze_type;
    }

    public function setClozeType(int $cloze_type): void
    {
        $this->cloze_type = $cloze_type;
    }

    public function getPoints(): float
    {
        return $this->points;
    }

    public function setPoints(float $points): void
    {
        $this->points = $points;
    }

    public static function loadAnswersByQuestionTypeAndQuestionId(string $question_type, int $questions_id): array
    {
        $answers = [];

        $database = new DHBWTrainingDatabase();

        $table = "";

        switch ($question_type) {
            case 'assSingleChoice':
                $table = "qpl_a_sc";
                break;
            case 'assMultipleChoice':
                $table = "qpl_a_mc";
                break;
            case 'assClozeText':
                $table = "qpl_a_cloze";
                break;
        }

        $result = $database->select($table, [
            "question_fi" => $questions_id
        ]);

        foreach ($result as $row) {
            $question_answer = new self();

            $question_answer->setQuestionId((int) $row['question_fi']);
            $question_answer->setAnswerId((int) $row['answer_id']);
            $question_answer->setAnswertext($row['answertext']);
            $question_answer->setAOrder((int) $row['aorder']);
            $question_answer->setPoints((float) $row['points']);

            if (isset($row['cloze_type'])) {
                $question_answer->setClozeType((int) $row['cloze_type']);

                $answers[$row['gap_id']]['cloze_type'] = $row['cloze_type'];
                $answers[$row['gap_id']][$row['aorder']] = $question_answer;
            } else {
                $answers[$row['aorder']] = $question_answer;
            }
        }

        return $answers;
    }
}