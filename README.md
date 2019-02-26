RandomContentGenerator, random content generator :smirk:
========================================================

RCG is a PHP class to be used as a mock Model layer. Its constructor takes a model array (see description below) and the expected collection size as parameters. Then you just have to call fetch() or fetchAll() on the generator and it will return one or many associative arrays containing randomly generated info.

```php
// create a new generator with this simple line
$gen = new RandomContentGenerator(['id' => 'i:0-10000', 'title' => 't:3-10w', 'illustration' => 'p:1500*900'], 30);
// here we asked for a collection of 30 arrays, each having an 'id', a 'title' and an 'illustration'
// you can ask for 4 different types of data, each with their own options, using the syntax 'type:options'
// integer [type i], the only option is a range to limit possible values (here between 0 and 10k), you can omit it, it will default to 0 - mt_getrandmax()
// float [type f], same option, the range, defaults to 0 - 1
// text [type t], you have to provide a length as option, either fixed or varying (with the dash), either in words (w) or in sentences (s)
// picture [type p], you have to provide width and height with an asterisk as separator

// get your data
$gen->fetchAll(); // all at once
// $gen->fetch(); // or line by line

// or get model instances directly
$gen->fetchAllObj(ArtworkModel::class); // all at once
// $gen->fetchObj(ArtworkModel::class); // or one by one

```

## Not fast enough ?

RCG comes with a built-in collection of 20 items with the following model :
- id : integer
- title : 4 to 6 words long text
- content : 5 sentences long text
- image : 400 by 300 picture

If you are happy with these settings, skip the parameters in the constructor ;-)

## Changelog

v1.0.0 - initial commit
v1.1.0 - fetching as objects is available

## Coming soon

- Date type
- Nested entity type

## Thanks

- BaconIpsum for the fun mix of meat and latin vocabulary I stole from their API :kissing_heart:
- LoremPicsum for their beautiful and stupidly simple API :kissing_heart:
