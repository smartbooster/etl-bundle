# SMARTBOOSTER - ETL bundle

[![Latest Stable Version](https://poser.pugx.org/smartbooster/etl-bundle/v/stable)](https://packagist.org/packages/smartbooster/etl-bundle)
[![Latest Unstable Version](https://poser.pugx.org/smartbooster/etl-bundle/v/unstable)](https://packagist.org/packages/smartbooster/etl-bundle)
[![Total Downloads](https://poser.pugx.org/smartbooster/etl-bundle/downloads)](https://packagist.org/packages/smartbooster/etl-bundle)
[![License](https://poser.pugx.org/smartbooster/etl-bundle/license)](https://packagist.org/packages/smartbooster/etl-bundle)

[![Build Status](https://api.travis-ci.org/smartbooster/etl-bundle.png?branch=master)](https://travis-ci.org/smartbooster/etl-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/smartbooster/etl-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/smartbooster/etl-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/smartbooster/etl-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/smartbooster/etl-bundle/?branch=master)

## Overview

ETL Bundle is a simple way to help you synchronize business data between databases.

### Use case

If your project code needs business data to work correctly you will have to maintain and test this data.
Synchronizing this data by hand is not reliable.

To benefits of git features like branches, history, blame... we usually store this kind of data into files directly in the code.
This allow us deploy exactly what we want and to synchronize the data with the corresponding code. And by the way it allow you to run your unit tests in your CI.

## Installation

### Add the bundle as dependency with Composer

``` bash
composer require smartbooster/etl-bundle
```

## Documentation

This bundle is structured around the [Extract Transform Load pattern (ETL) design pattern](https://en.wikipedia.org/wiki/Extract,_transform,_load).

- Extract
- [Transform](docs/transform.md)
- Load

- [Utils (to ease your implementation)](docs/utils.md)


## Contributing

Pull requests are welcome. 

Thanks to [everyone who has contributed](https://github.com/smartbooster/etl-bundle/contributors) already.
