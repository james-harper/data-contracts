#!/bin/bash

__list_cli_commands() {
    COMMANDS=`php cli list --raw --no-ansi | sed "s/[[:space:]].*//g"`
    COMPREPLY=(`compgen -W "$COMMANDS" -- "${COMP_WORDS[COMP_CWORD]}"`)
}

# php cli command tab completion
__cli_autocomplete() {
    if [ "${#COMP_WORDS[@]}" -lt "2" ]; then
        return;
    fi

    local word="${COMP_WORDS[COMP_CWORD]}"
    arg="${COMP_LINE#php }"

    case "$arg" in
        "cli"*)
            __list_cli_commands
        ;;
        *)
            COMPREPLY=( $(compgen -o default -- "${COMP_WORDS[COMP_CWORD]}") )
        ;;
    esac
} &&

complete -F __cli_autocomplete php
complete -F __list_cli_commands ./cli
