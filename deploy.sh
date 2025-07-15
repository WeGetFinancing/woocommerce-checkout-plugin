#!/bin/bash

# Define color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Define variables
proj_folder="/home/user/.proj/woocommerce-checkout-plugin"
svn_folder="/home/user/.proj/svn"

echo -e "${CYAN}🚀 Starting WordPress plugin deployment to SVN...${NC}"

# Step 1: Delete and recreate SVN folder
echo -e "${YELLOW}📁 Step 1: Preparing SVN folder...${NC}"
if [ -d "$svn_folder" ]; then
    echo -e "${RED}   Deleting existing SVN folder: $svn_folder${NC}"
    rm -rf "$svn_folder"
fi
echo -e "${GREEN}   Creating new SVN folder: $svn_folder${NC}"
mkdir -p "$svn_folder"

# Step 2: Change to SVN folder
echo -e "${YELLOW}📂 Step 2: Changing to SVN directory...${NC}"
cd "$svn_folder"
echo -e "${GREEN}   Current directory: $(pwd)${NC}"

# Step 3: SVN checkout
echo -e "${YELLOW}⬇️  Step 3: Checking out SVN repository...${NC}"
svn co https://plugins.svn.wordpress.org/wegetfinancing-payment-gateway
echo -e "${GREEN}   SVN checkout completed${NC}"

# Step 4: Change to plugin directory
echo -e "${YELLOW}📁 Step 4: Entering plugin directory...${NC}"
cd wegetfinancing-payment-gateway/
echo -e "${GREEN}   Current directory: $(pwd)${NC}"

# Step 5: Clear trunk directory
echo -e "${YELLOW}🧹 Step 5: Clearing trunk directory...${NC}"
rm -rf trunk/*
echo -e "${GREEN}   Trunk directory cleared${NC}"

# Step 6: Copy project files
echo -e "${YELLOW}📋 Step 6: Copying project files to trunk...${NC}"
cp -r "$proj_folder/." trunk/
echo -e "${GREEN}   Project files copied to trunk${NC}"

# Step 7: Remove wp folder
echo -e "${YELLOW}🗑️  Step 7: Removing wp folder...${NC}"
rm -rf trunk/wp/
echo -e "${GREEN}   wp folder removed${NC}"

# Step 8: Remove var/wp contents
echo -e "${YELLOW}🗑️  Step 8: Removing var/wp contents...${NC}"
if [ -d "trunk/var/wp" ]; then
    echo -e "${RED}   Removing contents of trunk/var/wp/...${NC}"
    rm -rf trunk/var/wp/*
    echo -e "${GREEN}   var/wp contents removed${NC}"
else
    echo -e "${BLUE}   No trunk/var/wp directory found${NC}"
fi

# Step 9: Remove node_modules if exists
echo -e "${YELLOW}🔍 Step 9: Checking for node_modules...${NC}"
if [ -d "trunk/node_modules" ]; then
    echo -e "${RED}   Removing node_modules directory${NC}"
    rm -rf trunk/node_modules
    echo -e "${GREEN}   node_modules removed${NC}"
else
    echo -e "${BLUE}   No node_modules directory found${NC}"
fi

# Step 10: Remove .phpcs-cache if exists
echo -e "${YELLOW}🔍 Step 10: Checking for .phpcs-cache...${NC}"
if [ -f "trunk/.phpcs-cache" ]; then
    echo -e "${RED}   Removing .phpcs-cache file${NC}"
    rm trunk/.phpcs-cache
    echo -e "${GREEN}   .phpcs-cache removed${NC}"
else
    echo -e "${BLUE}   No .phpcs-cache file found${NC}"
fi

# Step 11: Remove .gitignore if exists
echo -e "${YELLOW}🔍 Step 11: Checking for .gitignore...${NC}"
if [ -f "trunk/.gitignore" ]; then
    echo -e "${RED}   Removing .gitignore file${NC}"
    rm trunk/.gitignore
    echo -e "${GREEN}   .gitignore removed${NC}"
else
    echo -e "${BLUE}   No .gitignore file found${NC}"
fi

# Step 12: Remove .git directory if exists
echo -e "${YELLOW}🔍 Step 12: Checking for .git directory...${NC}"
if [ -d "trunk/.git" ]; then
    echo -e "${RED}   Removing .git directory${NC}"
    rm -rf trunk/.git
    echo -e "${GREEN}   .git directory removed${NC}"
else
    echo -e "${BLUE}   No .git directory found${NC}"
fi

# Step 13: Remove deploy.md if exists
echo -e "${YELLOW}🔍 Step 13: Checking for deploy.md...${NC}"
if [ -f "trunk/deploy.md" ]; then
    echo -e "${RED}   Removing deploy.md file${NC}"
    rm trunk/deploy.md
    echo -e "${GREEN}   deploy.md removed${NC}"
else
    echo -e "${BLUE}   No deploy.md file found${NC}"
fi

# Step 14: Remove .env if exists
echo -e "${YELLOW}🔍 Step 14: Checking for .env...${NC}"
if [ -f "trunk/.env" ]; then
    echo -e "${RED}   Removing .env file${NC}"
    rm trunk/.env
    echo -e "${GREEN}   .env removed${NC}"
else
    echo -e "${BLUE}   No .env file found${NC}"
fi

# Step 15: Remove .idea directory if exists
echo -e "${YELLOW}🔍 Step 15: Checking for .idea directory...${NC}"
if [ -d "trunk/.idea" ]; then
    echo -e "${RED}   Removing .idea directory${NC}"
    rm -rf trunk/.idea
    echo -e "${GREEN}   .idea directory removed${NC}"
else
    echo -e "${BLUE}   No .idea directory found${NC}"
fi

# Step 16: Remove deploy.sh if exists
echo -e "${YELLOW}🔍 Step 16: Checking for deploy.sh...${NC}"
if [ -f "trunk/deploy.sh" ]; then
    echo -e "${RED}   Removing deploy.sh file${NC}"
    rm trunk/deploy.sh
    echo -e "${GREEN}   deploy.sh removed${NC}"
else
    echo -e "${BLUE}   No deploy.sh file found${NC}"
fi

echo -e "${PURPLE}✅ All steps completed successfully!${NC}"
echo -e "${CYAN}📍 Current location: $(pwd)${NC}"
echo -e "${CYAN}📁 Trunk contents prepared for SVN commit${NC}"