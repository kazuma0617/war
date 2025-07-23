<?php
// 単一責任の原則：クラスは一つの責任を持つ

class Card{
    public $suit; //スート（トランプの絵柄）
    public $rank;
    private static $strength = [
        '2' => 2, '3' => 3, '4' => 4, '5' => 5,
        '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        '10' => 10, 'J' => 11, 'Q' => 12, 'K' => 13, 'A' => 14
    ];

    public function __construct($suit, $rank) {
        $this->suit = $suit;
        $this->rank = $rank;
    }

    public function getStrength() {
        return self::$strength[$this->rank];
    }

    //インスタンスがechoなどsting型が要求されえたとき呼ばれる。
    public function __toString() {
        return "{$this->suit}の{$this->rank}";
    }

}

class Deck{
    private $cards = [];
    public function __construct() {
        $suits = ['ハート', 'ダイヤ', 'クラブ', 'スペード'];
        $ranks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $this->cards[] = new Card($suit, $rank);
            }
        }
    }

    public function shuffle() {
        shuffle($this->cards);
    }

    //トランプの山札をプレイヤーに均等に配る処理
    public function deal($numPlayers) {
        $hands = array_fill(0, $numPlayers, []); //array_fill — 配列を指定した値で埋める
        foreach ($this->cards as $index => $card) {
            $hands[$index % $numPlayers][] = $card;
        }
        return $hands;
    }
}

class Player{
    public $name;
    public $hand;

    public function __construct($name, $hand) {
        $this->name = $name;
        $this->hand = $hand;
    }

    public function drawCard() {
        return array_shift($this->hand);
    }

    public function addCards($cards) {
        $this->hand = array_merge($this->hand, $cards); //勝ったプレイヤーが場札を全部もらう
    }

}

class Game{
    private $player1;
    private $player2;

    public function __construct() {
        $deck = new Deck();
        $deck->shuffle();
        [$hand1, $hand2] = $deck->deal(2);
        $this->player1 = new Player("プレイヤー1", $hand1);
        $this->player2 = new Player("プレイヤー2", $hand2);
    }

    public function play() {
        echo "戦争を開始します。\n";
        echo "カードが配られました。\n";
        sleep(2);

        $pile = [];//場に出たカードを貯める
        $this->battle($pile);

        echo "戦争を終了します。\n";
    }

    private function battle(&$pile) {
        echo "戦争！\n";

        $card1 = $this->player1->drawCard();
        $card2 = $this->player2->drawCard();

        echo "{$this->player1->name}のカードは{$card1}です。\n";
        sleep(2);
        echo "{$this->player2->name}のカードは{$card2}です。\n";
        sleep(2);

        $pile[] = $card1;
        $pile[] = $card2;

        $strength1 = $card1->getStrength();
        $strength2 = $card2->getStrength();

        if ($strength1 > $strength2) {
            echo "{$this->player1->name}が勝ちました。\n";
            $this->player1->addCards($pile);
        } elseif ($strength2 > $strength1) {
            echo "{$this->player2->name}が勝ちました。\n";
            $this->player2->addCards($pile);
        } else {
            echo "引き分けです。\n";
            // 引き分け時は追加カードで再戦（1回だけ）
            $this->battle($pile);
        }
    }

}

$game = new Game;
$game->play();