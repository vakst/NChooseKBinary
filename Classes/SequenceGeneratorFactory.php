<?php

class SequenceGeneratorFactory {
    public function create($placeAmount) {
        if (PHP_INT_SIZE * 8 - 1 >= $placeAmount) {
        	return new IntegerSequenceGenerator($placeAmount);
        } else {
        	return new GMPSequenceGenerator($placeAmount);
        }
    }
}