<?php

namespace App\Services;

class CryptService
{
    private string $key;

    public function __construct(string $key)
    {
        if (strlen($key) < 2) {
            throw new \InvalidArgumentException("Encryption key must be at least 2 bytes long.");
        }
        $this->key = $key;
    }

    /**
     * Порт функции CRYPT() из Harbour Project / CA-Clipper.
     * Эта функция является симметричной: она шифрует и расшифровывает данные.
     *
     * @param string $string Исходная строка (зашифрованная или открытая)
     * @return string
     */
    public function crypt(string $string): string
    {
        $nCryptLen = strlen($this->key);
        $pbyCrypt = array_values(unpack('C*', $this->key));
        $nCryptPos = 0;

        $pbyString = array_values(unpack('C*', $string));
        $nStringLen = count($pbyString);

        $pbyResult = [];

        // Инициализация внутренних счетчиков (состояния шифра)
        $uiCount2 = ((ord($this->key[0]) + (ord($this->key[1]) * 256)) & 0xFFFF) ^ ($nCryptLen & 0xFFFF);
        $uiCount1 = 0xAAAA;

        for ($nStringPos = 0; $nStringPos < $nStringLen;) {
            $uiTmpCount1 = $uiCount1;
            $uiTmpCount2 = $uiCount2;

            $byte = $pbyString[$nStringPos] ^ $pbyCrypt[$nCryptPos++];

            // --- Начало сложной логики изменения состояния ---

            $lo = $uiTmpCount2 & 0xFF;
            $hi = ($uiTmpCount2 >> 8) & 0xFF;
            $uiTmpCount2 = (($lo ^ $hi) & 0xFF) | ($hi << 8);

            for ($tmp = ($uiTmpCount2 & 0xFF); $tmp > 0; $tmp--) {
                $uiTmpCount2 = ($uiTmpCount2 >> 1) | (($uiTmpCount2 & 1) << 15);
            }

            $uiTmpCount2 = ($uiTmpCount2 ^ $uiTmpCount1) & 0xFFFF;
            $uiTmpCount2 = ($uiTmpCount2 + 16) & 0xFFFF;

            $uiCount2 = $uiTmpCount2;

            $uiTmpCount2 &= 0x1E;
            $uiTmpCount2 += 2;

            do {
                $uiTmpCount2--;

                for ($tmp = ($uiTmpCount2 & 0xFF); $tmp > 0; $tmp--) {
                    $uiTmpCount1 = ($uiTmpCount1 >> 1) | (($uiTmpCount1 & 1) << 15);
                }

                $lo = $uiTmpCount1 & 0xFF;
                $hi = ($uiTmpCount1 >> 8) & 0xFF;
                $uiTmpCount1 = ($hi & 0xFF) | ($lo << 8);

                $lo = ($uiTmpCount1 & 0xFF) ^ 0xFF;
                $hi = ($uiTmpCount1 >> 8) & 0xFF;
                $uiTmpCount1 = ($lo & 0xFF) | ($hi << 8);

                $uiTmpCount1 = ($uiTmpCount1 << 1) | (($uiTmpCount1 & 0x8000) >> 15);
                $uiTmpCount1 &= 0xFFFF;
                $uiTmpCount1 ^= 0xAAAA;

                $byTmp = $uiTmpCount1 & 0xFF;
                $byTmp = (($byTmp << 1) | (($byTmp & 0x80) >> 7)) & 0xFF;

                $hi = ($uiTmpCount1 >> 8) & 0xFF;
                $uiTmpCount1 = ($byTmp & 0xFF) | ($hi << 8);
            } while (--$uiTmpCount2 > 0);

            $uiCount1 = $uiTmpCount1;


            $pbyResult[$nStringPos++] = $byte ^ ($uiTmpCount1 & 0xFF);

            if ($nCryptPos == $nCryptLen) {
                $nCryptPos = 0;
            }
        }

        $resultString = '';
        foreach ($pbyResult as $char_code) {
            $resultString .= chr($char_code);
        }

        return $resultString;
    }
}
