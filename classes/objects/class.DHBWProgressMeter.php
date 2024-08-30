<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace objects;

/**
 * Class DHBWProgressMeter
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWProgressMeter {
    const PROGRESS_METER_TYPE_STANDARD = "STANDARD";
    const PROGRESS_METER_TYPE_MINI = "MINI";
    private string $progressmeter_type;
    private string $title;
    private int $max_width_in_pixel;
    private int $max_reachable_score;

    private float $required_score;

    private ?string $required_score_label = null;

    private float $primary_reached_score;

    private ?string $primary_reached_score_label = null;
    private ?int $secondary_reached_score = null;
    private ?string $secondary_reached_score_label = null;


    public static function newFromArray(array $arr_progress_meter = null) : DHBWProgressMeter
    {
        $obj = new static();

        foreach ($arr_progress_meter as $property => $value) {
            if (property_exists($obj, $property)) {
                $obj->{$property} = $value;
            }
        }

        //Default-Values
        if ($obj->max_width_in_pixel == 0) {
            $obj->max_width_in_pixel = 200;
        }

        return $obj;
    }


    public static function newStandardProgressMeter(
        string $title,
        int $max_width_in_pixel,
        int $max_reachable_score,
        float $required_score,
        string $required_score_label,
        int $primary_reached_score,
        string $primary_reached_score_label,
        int $secondary_reached_score,
        string $secondary_reached_score_label
    ) : DHBWProgressMeter {
        $obj = new static();
        $obj->title = $title;
        $obj->max_width_in_pixel = $max_width_in_pixel;
        $obj->progressmeter_type = static::PROGRESS_METER_TYPE_STANDARD;
        $obj->max_reachable_score = $max_reachable_score;
        $obj->required_score = $required_score;
        $obj->required_score_label = $required_score_label;
        $obj->primary_reached_score = $primary_reached_score;
        $obj->primary_reached_score_label = $primary_reached_score_label;
        $obj->secondary_reached_score = $secondary_reached_score;
        $obj->secondary_reached_score_label = $secondary_reached_score_label;

        return $obj;
    }


    public static function newMiniProgressMeter(
        string $title,
        int $max_width_in_pixel,
        int $max_reachable_points,
        int $required_score,
        int $primary_reached_score
    ) : DHBWProgressMeter {
        $obj = new static();
        $obj->title = $title;
        $obj->max_width_in_pixel = $max_width_in_pixel;
        $obj->progressmeter_type = static::PROGRESS_METER_TYPE_MINI;
        $obj->max_reachable_score = $max_reachable_points;
        $obj->required_score = $required_score;
        $obj->primary_reached_score = $primary_reached_score;

        return $obj;
    }

    public function getProgressmeterType(): string
    {
        return $this->progressmeter_type;
    }

    public function setProgressmeterType(string $progressmeter_type): void
    {
        $this->progressmeter_type = $progressmeter_type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getMaxWidthInPixel(): int
    {
        return $this->max_width_in_pixel;
    }

    public function setMaxWidthInPixel(int $max_width_in_pixel): void
    {
        $this->max_width_in_pixel = $max_width_in_pixel;
    }

    public function getMaxReachableScore(): int
    {
        return $this->max_reachable_score;
    }

    public function setMaxReachableScore(int $max_reachable_score): void
    {
        $this->max_reachable_score = $max_reachable_score;
    }

    public function getRequiredScore(): float
    {
        return $this->required_score;
    }

    public function setRequiredScore(float $required_score): void
    {
        $this->required_score = $required_score;
    }

    public function getRequiredScoreLabel(): ?string
    {
        return $this->required_score_label;
    }

    public function setRequiredScoreLabel(?string $required_score_label): void
    {
        $this->required_score_label = $required_score_label;
    }

    public function getPrimaryReachedScore(): float
    {
        return $this->primary_reached_score;
    }

    public function setPrimaryReachedScore(int $primary_reached_score): void
    {
        $this->primary_reached_score = $primary_reached_score;
    }

    public function getPrimaryReachedScoreLabel(): ?string
    {
        return $this->primary_reached_score_label;
    }

    public function setPrimaryReachedScoreLabel(?string $primary_reached_score_label): void
    {
        $this->primary_reached_score_label = $primary_reached_score_label;
    }

    public function getSecondaryReachedScore(): ?int
    {
        return $this->secondary_reached_score;
    }

    public function setSecondaryReachedScore(?int $secondary_reached_score): void
    {
        $this->secondary_reached_score = $secondary_reached_score;
    }

    public function getSecondaryReachedScoreLabel(): ?string
    {
        return $this->secondary_reached_score_label;
    }

    public function setSecondaryReachedScoreLabel(?string $secondary_reached_score_label): void
    {
        $this->secondary_reached_score_label = $secondary_reached_score_label;
    }
}