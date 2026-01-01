<?php
class Card
{
    private $id;
    private $image;
    public $flipped = false;
    public $matched = false;

    public function __construct($id, $image)
    {
        $this->id = $id;
        $this->image = $image;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getImage()
    {
        return $this->image;
    }

    // Méthodes ajoutées pour la nouvelle version
    public function isMatched()
    {
        return $this->matched;
    }

    public function setMatched($matched)
    {
        $this->matched = $matched;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'image' => $this->image,
            'matched' => $this->matched
        ];
    }

    public static function fromArray($data)
    {
        $card = new Card($data['id'], $data['image']);
        $card->matched = $data['matched'];
        return $card;
    }
}