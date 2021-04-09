---
title: "Automated Testing"
order: "1900"
---

As of Timber 2.x we have a new, experimental dev environment setup that uses [Lando](https://docs.lando.dev/). Unlike Vagrant/VVV, Lando is built on top of Docker Compose, and thus is completely self-contained. Full support for

## Setup a Lando dev environment (experimental)

### 1. Install Lando

Follow Lando's [Installation](https://docs.lando.dev/basics/installation.html) guide, making sure your system meets their system requirements. If you run Linux, Mac, or Windows, chances are you will be fine.

Docker is a prerequisite, but if you're on Mac or Windows, the Lando installer will install Docker for you.

Running `lando version` should print the version number once installed.

### 2. Start Lando

Now you're ready to rock:

```
lando start
```

This will take a few minutes (but should still be faster than booting VVV from scratch). A helper script running inside Lando's main WordPress container will install WordPress core, the WordPress Unit Test Suite, and create an admin user with the following info:

* Username: `timber`
* Password: `timber`
* Email: `timber@example.com`

It will also install dev dependencies, such as PHPUnit, and plugins we integrate with directly:

* Advanced Custom Fields
* CoAuthors Plus
* Carbon Fields (work in progress)

### 3. Start hacking

Out of the box, the Lando environment has a few shortcuts for quickly running miscellaneous dev commands. See [Lando Tooling](https://docs.lando.dev/guides/lando-101/lando-tooling.html) to learn how this works.

#### Unit tests

The `lando phpunit` command wraps PHPUnit, which is installed automatically:

```sh
# Run the whole suite:
lando phpunit
# Run tests tagged with the posts-api @group:
lando phpunit --group posts-api
```

Accepts any args that `phpunit` (7.x) normally accepts.

#### Coding standards

These commands analyze (and/or fix) Timber's adherence to coding standards, which are decalared in phpcs.xml.

```sh
# Run all standards checks:
lando phpcs
# Summarize standards violations:
lando phpcs-summary
# Fix any standards violations that are possible to fix automatically:
lando phpcs-fix
```

#### Static Analysis

PHPStan can help find potential bugs and unsafe code:

```sh
lando phpstan
```

## Setup a testing environment with PHPUnit (standard)

Follow the setup steps for [Varying Vagrant Vagrants](https://github.com/Varying-Vagrant-Vagrants/VVV). If you’re having trouble I recommend starting 100% fresh (uninstall and delete everything including VirtualBox). For this walk-through we’re going to start from scratch:

### 1. Install VVV

Follow the [setup instructions](https://varyingvagrantvagrants.org/docs/en-US/installation/) — don't forget to install VirtualBox and Vagrant first! For this tutorial I'm going to assume you've installed into `~/vagrant-local/`

### 2. Set up Timber for tests

Navigate into the `www` directory and clone Timber...

```
$ cd ~/vagrant-local/www/
$ git clone git@github.com:timber/timber.git
```

### 3. Install WordPress's Development Version
VVV no longer installs the development version of WordPress by default so we have to re-provision with it enabled. Edit `vvv-custom.yml` and find this line:

```
  wordpress-trunk:
    skip_provisioning: true # provisioning this one takes longer, so it's disabled by default
```

Set `skip_provisioning` to `false`
```
  wordpress-trunk:
    skip_provisioning: false # provisioning this one takes longer, so it's disabled by default
```

Now re-provision Vagrant

```
$ vagrant halt && vagrant up --provision
```

Warning: this one will take a while.

### 4. Configure WordPress tests
Copy `/wordpress-trunk/public_html/wp-tests-config-sample.php` to `/wordpress-trunk/public_html/wp-tests-config.php`. Assuming you're using VVV's defaults, we just need to specify how to access the database:

```php
define( 'DB_NAME', 'wordpress_unit_tests' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
```

### 5. Install WordPress tests
SSH into Vagrant to install Timber's tests and run them

```
$ vagrant ssh
```

Now, navigate to where you installed Timber via Git:

```
$ cd /srv/www/timber
```

And install the tests!

```
$ bin/install-wp-tests.sh wordpress_tests root root
```

All done! Now, the fun part (and the only part you have to do in the future when writing/running tests)

### 6. Run the tests!

Connect to your Vagrant instance trough SSH (if you're not already):

```
$ vagrant ssh
```

Now wait for it to bring you into the virtual box from the virtual environment...

```
$ cd /srv/www/timber
$ composer install
$ phpunit
```

You should see a bunch of gobbledygook across your screen (the whole process will take about 4 mins.), but we should see that WordPress is testing successfully. Hurrah! For more info, check out the [Handbook on Automated Testing](http://make.wordpress.org/core/handbook/automated-testing/).

### Writing tests

Now we get to the good stuff. You can add tests to the `timber/tests` directory. Any new features should be covered by tests. You can be a hero and help write tests for existing methods and functionality.

### Gotchas!

- You may need to setup authorization between VVV and GitHub. Just follow the prompts to create a token if that interrupts the `composer install`.
- You may have [memory problems with Composer](https://getcomposer.org/doc/articles/troubleshooting.md#proc-open-fork-failed-errors). In case that happens, here’s the script I run:

```
sudo /bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024
sudo /sbin/mkswap /var/swap.1
sudo /sbin/swapon /var/swap.1
```

## Static Analysis

Version 2.x is also covered by static analysis courtesy of [PHPStan](https://github.com/phpstan/phpstan). This analyzes our code files and structure to ensure things like existence and accessibility of called methods and functions, number of passed arguments, data types in docs, etc.

To run, use:

```
vendor/bin/phpstan analyze -l 1
```

Where the last argument is the level of [strictness](https://medium.com/@ondrejmirtes/phpstan-0-12-released-f1a88036535d) (0-7) to apply. We're currently starting at level 1 and working our way up over time.



