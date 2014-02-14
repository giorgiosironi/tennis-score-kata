<?php

class GameTest extends \PHPUnit_Framework_TestCase
{
    public function testTheInitialScoreIs0To0()
    {
        $game = new Game();
        $this->assertEquals("0 - 0", $game->score());
    } 

    public function testAfterAPlayerScoresHeGoesOn15To0()
    {
        $game = new Game();
        $game->aScores();
        $this->assertEquals("15 - 0", $game->score());
    } 

    public function testAfterAAndBPlayersScoresTheyAre15To15()
    {
        $game = new Game();
        $game->aScores();
        $game->bScores();
        $this->assertEquals("15 - 15", $game->score());
    } 

    public function testAfterAScoresTwiceHeIsWinning30To0()
    {
        $game = new Game();
        $game->aScores();
        $game->aScores();
        $this->assertEquals("30 - 0", $game->score());
    } 

    public function testAfterAScoresThreeTimesHeIsWinning40To0()
    {
        $game = new Game();
        $game->aScores();
        $game->aScores();
        $game->aScores();
        $this->assertEquals("40 - 0", $game->score());
    } 

    public function testAfterAScoresThreeTimesHeHasWonTheGame()
    {
        $game = new Game();
        $game->aScores();
        $game->aScores();
        $game->aScores();
        $game->aScores();
        $this->assertEquals("W - 0", $game->score());
    } 

    public function testAfterADeuceAScoringBringsHimTheAdvantage()
    {
        $game = new Game();
        $game->aScores();
        $game->aScores();
        $game->aScores();
        $game->bScores();
        $game->bScores();
        $game->bScores();

        $game->aScores();

        $this->assertEquals("A - 40", $game->score());
    } 

    public function testScoringWhileInAdvantageIsWinningTheGame()
    {
        $game = new Game();
        $game->aScores();
        $game->aScores();
        $game->aScores();
        $game->bScores();
        $game->bScores();
        $game->bScores();

        $game->aScores();
        $game->aScores();

        $this->assertEquals("W - 40", $game->score());
    } 

    public function testScoringWhileTheOtherPlayerIsInAdvantageBringsBackTheGameToDeuce()
    {
        $game = new Game();
        $game->aScores();
        $game->aScores();
        $game->aScores();
        $game->bScores();
        $game->bScores();
        $game->bScores();

        $game->aScores();
        $game->bScores();

        $this->assertEquals("40 - 40", $game->score());
    } 
}

class Game
{
    private $aScore;
    private $bScore;

    public function __construct()
    {
        $this->aScore = new NormalScore(0);
        $this->bScore = new NormalScore(0);
    }

    public function aScores()
    {
        $aScore = $this->aScore;
        $bScore = $this->bScore;
        $this->aScore = $aScore->yourPoint($bScore);
        $this->bScore = $bScore->otherPoint($aScore);
    }

    public function bScores()
    {
        $aScore = $this->aScore;
        $bScore = $this->bScore;
        $this->bScore = $bScore->yourPoint($aScore);
        $this->aScore = $aScore->otherPoint($bScore);
    }

    public function score()
    {
        return new Scores(['a' => $this->aScore, 'b' => $this->bScore]);
    }

}

interface Score
{
    public function yourPoint(Score $otherScore);

    public function otherPoint(Score $otherScore);
}

class NormalScore implements Score
{
    const MAXIMUM = 2;
    private $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function yourPoint(Score $otherScore)
    {
        if ($this->value == self::MAXIMUM) {
            return new Score40();
        }
        return new self($this->value + 1);
    }

    public function otherPoint(Score $otherScore)
    {
        return $this;
    }

    public function when40ScoresAgainstYou()
    {
        return new ScoreW();
    }

    public function __toString()
    {
        switch ($this->value) {
            case 0:
                return '0';
            case 1:
                return '15';
            case 2:
                return '30';
        }
    }
}

class Score40 implements Score
{
    public function yourPoint(Score $otherScore)
    {
        return $otherScore->when40ScoresAgainstYou();
    }

    public function otherPoint(Score $otherScore)
    {
        return $this;
    }

    public function when40ScoresAgainstYou()
    {
        return new AdvantageScore();
    }

    public function __toString()
    {
        return '40';
    }
}

class ScoreW implements Score
{
    public function yourPoint(Score $otherScore)
    {
        throw new BadMethodCallException();
    }

    public function otherPoint(Score $otherScore)
    {
        throw new BadMethodCallException();
    }

    public function when40ScoresAgainstYou()
    {
        throw new BadMethodCallException();
    }

    public function __toString()
    {
        return 'W';
    }
}

class AdvantageScore implements Score
{
    public function yourPoint(Score $otherScore)
    {
        return new ScoreW();
    }

    public function otherPoint(Score $otherScore)
    {
        return new Score40();
    }

    public function when40ScoresAgainstYou()
    {
        return new Score40();
    }

    public function __toString()
    {
        return 'A';
    }
}

class Scores
{
    private $byPlayer;

    public function __construct(array $byPlayer)
    {
        $this->byPlayer = $byPlayer;
    }

    public function __toString()
    {
        return implode(" - ", $this->byPlayer);
    }
}
