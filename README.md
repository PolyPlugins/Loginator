# Loginator
Debugging WordPress can sometimes be a pain, our goal is to make it easy, which is why Loginator was built with this in mind. From creating a log folder, to securing it from prying eyes, Loginator is here to save you time and resources, so you can focus on creating astonishing applications. Once activated, Loginator essentially becomes a core part of WordPress, which is why we disable deactivation as it is highly recommended to not uninstall Loginator until you have removed all references to the loginator function inside your WordPress installation. **We recommend setting this as a Must Use plugin to further prevent deactivation as this will trigger errors if any instances of logging are left after deactivation. We recommend testing this plugin in your staging environment to make sure it's fully compatible with your WordPress installation.**

# Features
- Incorporates our [Settings Class for WordPress](https://github.com/PolyPlugins/Settings-Class-for-Wordpress "Settings Class for WordPress")
- OOP port of our previous [Loginator](https://wordpress.org/plugins/loginator/) plugin
- Global Enable/Disable
- Flags for Errors, Debug, and Info
- Creates separate files based on flags
- Auto detect if data being logged is an array and pretty prints it to the file
- Disable Loginator deactivation to prevent function not existing errors
- Email on CRITICAL and EMERGENCY flag
- Pipe Dream logging
- Our beautiful comments follow WordPress Developer Standards, that when paired with Visual Studio Code or other supporting IDE’s will elaborately explain how to use the loginator methods.

# Usage
```
Loginator::emergency('log data here'); // Email triggers to site admin or configured emails
Loginator::alert('log data here');
Loginator::critical('log data here'); // Email triggers to site admin or configured emails
Loginator::error('log data here');
Loginator::warning('log data here');
Loginator::notice('log data here');
Loginator::info('log data here');
Loginator::debug('log data here'); // PipeDream flag is set to true by default
```

You can also pass arguments

```
$args = array(
  'flag'      => 'd',
  'id'        => 23,
  'file'      => 'test',
  'pipedream' => false,
);

Loginator::info('log data here', $args);
```

# Upgrading
Since Loginator 2.0 is essentially rebuilt from the ground up using OOP, we wouldn't advise upgrading just yet until we incorporate a method for handling the old loginator function, otherwise you'll have to manually update all of the old loginator functions to the new static methods.
