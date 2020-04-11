<?php 

namespace GilmarBrito\Classes;

class CaesarCipher
{
    private $_displacement;
    private $_encryptedMessage;
    private $_decryptedMessage;
    private $_decryptedMessageSha1;
    

    public function __construct(int $displacement, string $encryptedMessage)
    {
        $this->_displacement = $displacement;
        $this->_encryptedMessage = strtolower($encryptedMessage);
        $this->_decryptedMessage = '';
        $this->_decryptedMessageSha1 = '';
    }

    private function letterToNumber(string $letter)
    {
        $result = ord(strtolower($letter)) - 96;
        $result--;
        return $result;
    }

    private function numberToLetter(int $number)
    {
        $number++;
        $result = strtolower(chr($number + 64));
        return $result;
    }

    private function endecryptLetter($letter, $displacement) : string
    {
        $number = $this->letterToNumber($letter);

        if (($number + $displacement > 25)) {
            $number = $number + $displacement - 26;
        } else {
            $number += $displacement;
        }

        return $this->numberToLetter($number);
    }

    private function decryptLetter($letter, $displacement) : string
    {
        $number = $this->letterToNumber($letter);
    
        if (($number - $displacement < 0)) {
            $number = $number - $displacement + 26;
        } else {
            $number -= $displacement;
        }

        return $this->numberToLetter($number);
    }


    public function getDecryptedMessage()
    {
        $message = str_split($this->_encryptedMessage);
        $displacement = $this->_displacement;
        foreach ($message as $char) {
            
            $char = ctype_alpha($char) ? $this->decryptLetter($char, $displacement) : $char;
            $this->_decryptedMessage .= $char;
        }

        return $this->_decryptedMessage; 
    }
    
    public function getSha1HashMessage()
    {
        $this->_decryptedMessageSha1 = sha1($this->_decryptedMessage);
        return $this->_decryptedMessageSha1;
    }
}
