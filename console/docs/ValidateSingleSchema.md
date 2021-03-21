This command validates a {JSON Schema} against the specification.

Only the schema {name} is needed as an argument.
For example:
`php cli validate:schema account`

The {name} argument is _NOT_ case sensitive.

The schema validation is being done using a Composer package called
`opis/json-schema`.

_Useful links:_
- `https://opis.io/json-schema/1.x/`
- `https://json-schema.org/`
