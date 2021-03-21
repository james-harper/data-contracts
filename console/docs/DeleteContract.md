This is a command to deletes an existing data contract.

It will remove the php {DataContract} definition, the {JSON Schema}
definition, and the {example JSON implentation}.
It will remove any {tests} that were written for the contract and
will update the {{validate:all}} console command so that it no
longer tries to look for this contract as part of the validation
process.
