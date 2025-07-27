<?php
// 単一責任の原則：クラスは一つの責任を持つ
// 必要なオブジェクト（人、カード、デッキ、ゲーム）

class Card{
    public $suit; //スート（トランプの絵柄）
    public $rank;
    private static $strength = [ // static インスタンス化を必要としない
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
        shuffle($this->cards);
    }

    //トランプの山札をプレイヤーに均等に配る処理
    public function deal($numPlayers) { // deal 取引
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
    private $stock = [];

    public function __construct($name, $hand) {
        $this->name = $name;
        $this->hand = $hand;
    }

    // 手札がなくなるとストックのカードを追加
    public function drawCard() {
        if(empty($this->hand)){
            $this->reloadHandFromStock();
        }
        return array_shift($this->hand);
    }

    public function addCards($cards) {
        $this->stock = array_merge($this->stock, $cards);
    }

    private function reloadHandFromStock() {
        if (!empty($this->stock)) {
            shuffle($this->stock);
            $this->hand = $this->stock;
            $this->stock = [];
            echo "{$this->name}はストックから手札を補充しました。\n";
        }
    }

    // 手札が残っているか判断
    public function hasCards() {
        return count($this->hand) > 0 || count($this->stock) > 0;
    }

    public function cardCount() {
        return count($this->hand) + count($this->stock);
    }

}

class Game{
    // 手札がなくなるまでループで戦争を繰り返すようにします。

// 引き分けの場合は手札がある限り再戦。

// 勝敗が決まったらそのプレイヤーが場のカードを獲得。

// 誰かの手札がなくなった時点で、残りカード枚数から順位を表示します。
    private $player1;
    private $player2;

    public function __construct() {
        $deck = new Deck();
        [$hand1, $hand2] = $deck->deal(2);
        $this->player1 = new Player("プレイヤー1", $hand1);
        $this->player2 = new Player("プレイヤー2", $hand2);
    }

    public function play() {
        echo "戦争を開始します。\n";
        echo "カードが配られました。\n";

        // 手札がなくなるまでループで戦争を繰り返す
        while($this->player1->hasCards() && $this->player2->hasCards()){
            $pile = [];
            $this->battle($pile);
        }

        // 誰かの手札がなくなった時点で、残りカード枚数から順位を表示します。
        $this->printResult();
    }

    private function battle(&$pile) {
        echo "戦争！\n";

        $card1 = $this->player1->drawCard();
        $card2 = $this->player2->drawCard();

        echo "{$this->player1->name}のカードは{$card1}です。\n";
        echo "{$this->player2->name}のカードは{$card2}です。\n";

        $pile[] = $card1;
        $pile[] = $card2;

        $strength1 = $card1->getStrength();
        $strength2 = $card2->getStrength();

        // 勝敗が決まったらそのプレイヤーが場のカードを獲得。
        if ($strength1 > $strength2) {
            echo "{$this->player1->name}が勝ちました。{$this->player1->name}はカードを" . count($pile) . "枚もらいました。\n";
            $this->player1->addCards($pile);
        } elseif ($strength2 > $strength1) {
            echo "{$this->player2->name}が勝ちました。{$this->player2->name}はカードを" . count($pile) . "枚もらいました。\n";
            $this->player2->addCards($pile);
        } else {
            echo "引き分けです。\n";
            // 引き分けの場合は手札がある限り再戦。
            if ($this->player1->hasCards() && $this->player2->hasCards()) {
                $this->battle($pile);
            } else {
                echo "どちらかの手札がなくなったため、引き分けのまま戦争を終了します。\n";
            }
        }
    }

    private function printResult() {
        echo "戦争を終了します。\n";

        $count1 = $this->player1->cardCount();
        $count2 = $this->player2->cardCount();

        echo "{$this->player1->name}の手札の枚数は{$count1}枚です。\n";
        echo "{$this->player2->name}の手札の枚数は{$count2}枚です。\n";

        if ($count1 > $count2) {
            echo "{$this->player1->name}が1位、{$this->player2->name}が2位です。\n";
        } elseif ($count2 > $count1) {
            echo "{$this->player2->name}が1位、{$this->player1->name}が2位です。\n";
        } else {
            echo "引き分けです。\n";
        }
    }

}

$game = new Game;
$game->play();