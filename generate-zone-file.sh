#!/bin/sh

# Usage:
# sh generate-zone-file.sh domain_name github_repo

# Example:
# sh generate-zone-file.sh productioncontrol.com. prodctrl/zone-file-example


# Command-line styles
style_reset="\e[0m"
style_success="\e[92m"
style_error="\e[1;91m"
style_advisory="\e[93m"
style_special="\e[1;94m"


# If required parameters are missing...
if [ -z "$1" -o -z "$2" ] ; then
	# Display an error message
	echo
	echo "${style_error}Required parameters missing!${style_reset}"
	echo
	echo "	${style_success}Example:"
	echo "		sh generate-zone-file.sh productioncontrol.com. prodctrl/zone-file-example${style_reset}"
	echo
	echo "	Syntax:"
	echo "		sh generate-zone-file.sh domain_name github_repo"
	echo

	# Exit
	exit 1
fi


# Assign parameters to variables
domain="$1"
domain_wo_period=$(echo "$domain" | sed s/.$//)
github_repo="$2"


# Paths
dir_folder="/tmp/zone-file"
dir_zone_file_repo="$dir_folder/repo"
dir_domain="$dir_folder/$domain_wo_period"
dir_github_repo="$dir_domain/repo"
dir_output="$dir_domain/`date +%Y-%m-%d-%H-%M-%S`"
zone_file_output="$dir_output/zone_file.txt"


sleep 1


# Delete directories
echo "${style_advisory}Deleting directories...${style_reset}"
rm -rf $dir_zone_file_repo/ $dir_github_repo/
sleep 1


# Make directories
echo
echo "${style_advisory}Creating directories...${style_reset}"
mkdir -p $dir_domain/ $dir_output/
sleep 1


# Clone repos
echo
echo "${style_advisory}Cloning prodctrl/zone-file.git...${style_reset}"
git clone git@github.com:prodctrl/zone-file.git $dir_zone_file_repo
sleep 1

echo
echo "${style_advisory}Cloning $github_repo.git...${style_reset}"
git clone git@github.com:$github_repo.git $dir_github_repo
sleep 1


# Generate zone file
echo
echo "${style_advisory}Generating zone file...${style_reset}"
php $dir_github_repo/zone-file-generator.php "$domain" "$github_repo" "$3" "$4" "$5" "$6" "$7" "$8" "$9" "$10" "$11" "$12" "$13" "$14" "$15" "$16" "$17" "$18" "$19" "$20" "$21" "$22" "$23" "$24" "$25" "$26" "$27" "$28" "$29" "$30" "$31" "$32" "$33" "$34" > $zone_file_output
sleep 1

echo
echo "${style_advisory}Zone file saved to $zone_file_output${style_reset}"
sleep 1

echo "${style_special}"
cat $zone_file_output
echo "${style_reset}"
sleep 1


# Push to Route 53?
while true; do
	read -p "Push to Route 53? (y/n) " push_to_route_53
	case $push_to_route_53 in
		[Yy]* )
			echo
			echo "${style_advisory}Pushing to Route 53...${style_reset}"
			cli53 import $domain_wo_period --file $zone_file_output --replace --wait
			sleep 1
			break;;
		[Nn]* )
			echo
			echo "${style_advisory}Zone file NOT pushed to Route 53${style_reset}"
			break;;
		* )
			echo
			echo "${style_error}Please answer \"y\" for yes or \"n\" for no${style_reset}"
	esac
done


# Done
echo
echo "${style_success}Done${style_reset}"