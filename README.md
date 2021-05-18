# SMARTBOOSTER - ETL bundle

[![Latest Stable Version](https://poser.pugx.org/smartbooster/etl-bundle/v/stable)](https://packagist.org/packages/smartbooster/etl-bundle)
[![Latest Unstable Version](https://poser.pugx.org/smartbooster/etl-bundle/v/unstable)](https://packagist.org/packages/smartbooster/etl-bundle)
[![Total Downloads](https://poser.pugx.org/smartbooster/etl-bundle/downloads)](https://packagist.org/packages/smartbooster/etl-bundle)
[![License](https://poser.pugx.org/smartbooster/etl-bundle/license)](https://packagist.org/packages/smartbooster/etl-bundle)

![CI workflow](https://github.com/smartbooster/etl-bundle/actions/workflows/ci.yml/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/smartbooster/etl-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/smartbooster/etl-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/smartbooster/etl-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/smartbooster/etl-bundle/?branch=master)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/870fd5e13d9f4befb3ff07c9d8eb26a8)](https://www.codacy.com/gh/smartbooster/etl-bundle/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=smartbooster/etl-bundle&amp;utm_campaign=Badge_Grade)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=smartbooster_etl-bundle&metric=alert_status)](https://sonarcloud.io/dashboard?id=smartbooster_etl-bundle)

[![GitHub contributors](https://img.shields.io/github/contributors/smartbooster/sonata-bundle.svg)](https://github.com/smartbooster/sonata-bundle/graphs/contributors)


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
