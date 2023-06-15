<p align="center">
  <img src="./art/logo.png" height="128" alt="" />
</p>
<h1 align="center">LaraDumps Core</h1>

<div align="center">
  <p align="center">
    <a href="https://packagist.org/packages/laradumps/laradumps-core">
      <img alt="Total Downloads" src="https://img.shields.io/packagist/dt/laradumps/laradumps-core">
    </a>
    <a href="https://packagist.org/packages/laradumps/laradumps-core">
      <img alt="Latest Version" src="https://img.shields.io/packagist/v/laradumps/laradumps-core">
    </a>
    <a href="https://github.com/laradumps/laradumps-core/actions">
        <img alt="Tests" src="https://github.com/laradumps/laradumps-core/workflows/LaraDumpsCore%20Tests/badge.svg" />
    </a>
    <a href="https://packagist.org/packages/laradumps-core/laradumps">
      <img alt="License" src="https://img.shields.io/github/license/laradumps/laradumps-core">
    </a>
  </p>
</div>

### 👋 Hello Dev,

<br/>

LaraDumps is a friendly app designed to boost your PHP coding and debugging experience.

When using LaraDumps, you can see the result of your debug displayed in a standalone Desktop application.

### Get Started

#### Requirements

PHP 8.0+

#### Usage

1. Download the 🖥️ [LaraDumps](https://github.com/laradumps/app) 2.0.0-alpha Desktop App here: [LaraDumps Desktop App](https://laradumps.dev/get-started/installation.html#desktop-app)
2. Install LaraDumps Core in your PHP project, run:

```shell
 composer require laradumps/laradumps-core --dev
 ```

3. Configure LaraDumps, run:

```shell
vendor/bin/laradumps configure
 ```

### Example

Here's an example:

```php
// File: routes/web.php

<?php 

ds('Hello from LaraDumps!');
```

### Credits

LaraDumps Core is a free open-source project, and it was inspired by [Spatie Ray](https://github.com/spatie/ray), check it out!

- Author: [Luan Freitas](https://github.com/luanfreitasdev)

- Logo by [Vitor S. Rodrigues](https://github.com/vs0uz4)
