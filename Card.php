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
}
