#!/bin/bash

# --- Script colors ---
RESET='\033[0m'
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'

# --- Echo Functions ---
echo_info() {
  echo -e "${CYAN}${1}${RESET}"
}

echo_success() {
  echo -e "${GREEN}${1}${RESET}"
}

echo_error() {
  echo -e "${RED}${1}${RESET}"
}

echo_warning() {
  echo -e "${YELLOW}${1}${RESET}"
}

echo_important() {
  echo -e "${MAGENTA}${1}${RESET}"
}

echo_final_success() {
  echo -e "${BLUE}${1}${RESET}"
}

# Function to generate a random password without dangerous characters
generate_pass() {
    apg -a 1 -m 40 -x 40 -M SNCL -n 1 \
      -E "'" -E '"' -E "\\" -E "%" -E "#" \
      -E "!" -E "\$" -E "&" -E "(" -E ")" \
      -E "*" -E "+" -E "," -E "/" -E ":" \
      -E ";" -E "<" -E "=" -E ">" -E "@" \
      -E "[" -E "]" -E "^" -E "\`" -E "{" \
      -E "|" -E "}" -E "~"  2>/dev/null | awk 'NR==1{print $1}'
}

