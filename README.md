# sequence
A simple sequence generator

[![Build Status](https://api.travis-ci.org/griffins/sequence.svg?branch=master)](https://travis-ci.org/griffins/sequence)

# installation

``` composer require griffins/sequence ```
    
# basics

This doc assumes you are autoloading the library via composer. 

```php
    
$allowedChars = 'ABCDEF0123456789';
//the only argument is an optional character dictonary, if not specified the default one is used. (0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ)
$sequence = \Sequence\Factory::create($allowedChars);

echo $sequence->next('<yyyy>/????');

```

Prints ```2018/0001```    


```<yyyy>, <mm>, <dd>``` are place holders for the current year,month and day respectively.

```php

$sequence->next('<yyyy>/????',null,'2018/AAAA')

```

Prints ```2018/AAAB```

The second argument is a callback that returns true or false to reject the generated sequence, when its returns false it causes a new sequence to be generated till the callback is satisfied or an overflow occours. For example trying to increment 9999 and the format used is limited to 4 characters.

The smart search optimizes usage of the callback by using binary search to boost perfomance. 


A good usage will be 

```php

$sequence = \Sequence\Factory::create();

$id =  $sequence->next('<yyyy>/????', function($id){
    //check if its exits in a dataset, 
    
    if($exists){
        return true;
    }else{
        // looks like we found a valid id
        return false;
    }
});
// now use the id generated

echo $id;
```
