<?php



/**
 * Non PSR-0/4 compliant class generating random content based on a provided model (for teaching purpose only)
 * 
 */
class RandomContentGenerator {

    private $volume;
    private $model=[];

    // trimmed, uniqued array of 131 words from BaconIpsum
    const WORDS_POOL = ["lorem", "capicola", "tenderloin", "sunt", "elit", "turducken", "sirloin", "eu", "ut", "pork", "chop", "est", "nisi", "cupim", "in", "culpa", "adipisicing", "beef", "incididunt", "id", "buffalo", "ea", "spare", "ribs", "t-bone", "meatball", "proident", "tail", "aute", "dolore", "tempor", "ipsum", "consectetur", "frankfurter", "exercitation", "voluptate", "esse", "porchetta", "ground", "round", "dolor", "excepteur", "quis", "cupidatat", "prosciutto", "aliqua", "leberkas", "meatloaf", "fugiat", "ball", "tip", "kevin", "duis", "nulla", "magna", "jerky", "qui", "deserunt", "sint", "sed", "turkey", "andouille", "officia", "anim", "sausage", "do", "reprehenderit", "rump", "filet", "mignon", "veniam", "et", "pig", "venison", "ad", "eiusmod", "flank", "doner", "labore", "minim", "non", "kielbasa", "chuck", "laboris", "commodo", "mollit", "ex", "occaecat", "swine", "biltong", "ham", "hock", "pariatur", "cillum", "belly", "salami", "velit", "laborum", "pancetta", "shank", "strip", "steak", "drumstick", "tri-tip", "short", "bacon", "ullamco", "jowl", "enim", "brisket", "burgdoggen", "picanha", "cow", "alcatra", "shankle", "loin", "tongue", "aliquip", "nostrud", "bresaola", "consequat", "boudin", "landjaeger", "fatback", "corned", "pastrami", "chicken", "ribeye", "irure", "hamburger", "shoulder"];

    // approximatively, the max id value for a random picture from LoremPicsum
    const PICSUM_MAX_ID = 1000;

    public function __construct($model=['id' => 'i', 'title' => 't:4-6w', 'content' => 't:5s', 'image' => 'p:400*300'], $volume=20) {
        $this->volume = $volume;
        foreach ($model as $prop => $type) {
            $this->model[$prop] = $this->_parseType($type);
        }
    }

    private function _parseType($typeString) {
        // initialization
        $typeArray = [];
        @list($type, $options) = explode(':', $typeString);

        // filter available types
        $supportedTypes = ['i' => 'int', 'f' => 'float', 't' => 'text', 'p' => 'image'];
        if (!isset($supportedTypes[$type]))
            throw new Exception("Type $type not supported in model declaration");
        $typeArray['type'] = $supportedTypes[$type];

        // parse options
        switch ($type) {
            case 'i':
                $typeArray['options'] = $this->_parseIntOptions($options);
                break;
            case 't':
                $typeArray['options'] = $this->_parseTextOptions($options);
                break;
            case 'p':
                $typeArray['options'] = $this->_parseImageOptions($options);
                break;
            case 'f':
                $typeArray['options'] = $this->_parseFloatOptions($options);
                break;
        }
        
        return $typeArray;
    }

    private function _parseIntOptions($optString) {
        // simple case 'i', no options
        if (is_null($optString)) return ['min' => 0, 'max' => mt_getrandmax()];

        // filter out bad options
        $optArray = [];
        @list($optArray['min'], $optArray['max']) = explode('-', $optString);
        if (
            !ctype_digit($optArray['min']) ||
            !ctype_digit($optArray['max']) ||
            $optArray['min'] > $optArray['max']
        )
            throw new Exception("Options $optString for integer type are invalid, min-max expected");

        return $optArray;
    }

    private function _parseFloatOptions($optString) {
        // simple case 'f', no options
        if (is_null($optString)) return ['min' => 0, 'max' => 1];

        // filter out bad options
        $optArray = [];
        @list($optArray['min'], $optArray['max']) = explode('-', $optString);
        if (
            !is_numeric($optArray['min']) ||
            !is_numeric($optArray['max']) ||
            $optArray['min'] > $optArray['max']
        )
            throw new Exception("Options $optString for float type are invalid, min-max expected");

        return $optArray;
    }

    private function _parseTextOptions($optString) {
        // decompose
        $unit = substr($optString, -1);
        $length = substr($optString, 0, -1);

        // filter out invalid unit
        $optArray = [];
        $supportedUnits = ['w' => 'words', 's' => 'sentences'];
        if (!isset($supportedUnits[$unit]))
            throw new Exception("Unit $unit is invalid for string length, use s(entences) or w(ords)");
        $optArray['unit'] = $supportedUnits[$unit];

        // filter out badly structured options
        @list($min, $max) = explode('-', $length);
        if (
            !ctype_digit($min) ||
            !ctype_digit($max) && !is_null($max) ||
            !is_null($max) && $min > $max
        )
            throw new Exception("Options $optString for string type are invalid, range or fixed number expected");

        // format array based on range or fixed number
        if (is_null($max))
            $optArray['length'] = $length;
        else
            $optArray += ['min' => $min, 'max' => $max];

        return $optArray;
    }

    private function _parseImageOptions($optString) {
        // filter out badly structured options
        $optArray = [];
        @list($optArray['width'], $optArray['height']) = explode('*', $optString);
        if (
            !ctype_digit($optArray['width']) ||
            !ctype_digit($optArray['height'])
        )
            throw new Exception("Options $optString for image type are invalid, width*height expected");

        return $optArray;
    }

    public function fetch() {
        return $this->_generateModel();
    }

    public function fetchObj($class) {
        $data = $this->_generateModel();
        $object = new $class();
        foreach ($data as $prop => $value) {
            $words = explode('_', $prop);
            array_walk($words, function(&$word) {
                $word = ucfirst($word);
            });
            $setter = "set".implode('', $words);
            if (!method_exists($class, $setter))
                throw new Exception("No setter found for '$prop' property on class '$class', expected $class::$setter(\$$prop)");
            $object->$setter($value);
        }
        return $object;
    }

    public function fetchAll() {
        $results = [];
        for ($i=0; $i < $this->volume; $i++) { 
            $results[] = $this->fetch();
        }
        return $results;
    }

    public function fetchAllObj($class) {
        $results = [];
        for ($i=0; $i < $this->volume; $i++) { 
            $results[] = $this->fetchObj($class);
        }
        return $results;
    }

    private function _generateModel() {
        $model = $this->model;

        foreach ($model as &$prop) {
            switch ($prop['type']) {
                case 'int':
                    $prop = $this->_generateIntProp($prop['options']);
                    break;
                case 'float':
                    $prop = $this->_generateFloatProp($prop['options']);
                    break;
                case 'text':
                    $prop = $this->_generateTextProp($prop['options']);
                    break;
                case 'image':
                    $prop = $this->_generateImageProp($prop['options']);
                    break;
            }
        }

        return $model;
    }

    private function _generateIntProp($propOptions) {
        return mt_rand($propOptions['min'], $propOptions['max']);
    }

    private function _generateFloatProp($propOptions) {
        return lcg_value() * ($propOptions['max']-$propOptions['min']) + $propOptions['min'];
    }

    private function _generateTextProp($propOptions) {

        // set the amount
        $amount = (isset($propOptions['min']))?mt_rand($propOptions['min'], $propOptions['max']):$propOptions['length'];

        // if it is words they want
        if ($propOptions['unit'] == "words") {
            $sentence = [];
            for ($i=0; $i < $amount; $i++) { 
                $sentence[] = self::WORDS_POOL[mt_rand(0, count(self::WORDS_POOL) - 1)];
            }
            // let them have words
            return implode(" ", $sentence);
        }
        // if it is sentences
        $sentences = [];
        for ($i=0; $i < $amount; $i++) { 
            // let them have tons of words
            $sentence = [];
            // every sentence will be 4-17 words long
            $currentSentenceLength = mt_rand(4, 17);
            for ($j=0; $j < $currentSentenceLength; $j++) {
                $sentence[] = self::WORDS_POOL[mt_rand(0, count(self::WORDS_POOL) - 1)];
            }
            $sentences[] = ucfirst(implode(" ", $sentence)).".";
        }
        return implode(" ", $sentences);
    }

    private function _generateImageProp($propOptions) {
        return "https://picsum.photos/{$propOptions['width']}/{$propOptions['height']}?image=".mt_rand(0, self::PICSUM_MAX_ID);
    }
}