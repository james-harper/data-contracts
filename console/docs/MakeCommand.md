This command is used to generate new commands. Very meta...

If a command is made with a name starting with {Make},
a `console/GeneratorCommand.php` will be created.
Any other commands will extend `console/BaseCommand.php`.
The convention is to suffix command files with `Command`.
However, this is not enforced.

_Examples:_
----------------------------------------------------------
Input                |   Output     |        Type
----------------------------------------------------------
{{MakeTestCommand}}      | {make:test}    | (`GeneratorCommand`)
{{DoSomethingCommand}}   | {do:something} | (`BaseCommand`)
{{ReadFile}}             | {read:file}    | (`BaseCommand`)
{{MakeFriend}}           | {make:friend}  | (`GeneratorCommand`)

The newly generated command class will be automatically
registered in the console kernel.
Documentation for the new command will also
be added to `console/Help`.
