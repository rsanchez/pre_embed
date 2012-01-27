# Pre Embed #

Embed a template before other tag parsing, so you can re-use templates more easily.

## Installation

* Copy the /system/expressionengine/third_party/pre_embed/ folder to your /system/expressionengine/third_party/ folder

## Usage
### The old way.
	{!--template--}
	{exp:channel:entries channel="your_channel"}
		{embed="site/embed" title="{title}" your_custom_field="{your_custom_field}"}
	{/exp:channel:entries}

	{!--embed--}
	<p>{embed:title}: {embed:your_custom_field}</p>


### The new way.
	{!--template--}
	{exp:pre_embed parse="inward"}{!--yes, parse="inward" is necessary--}
	{exp:channel:entries channel="your_channel"}
		{pre_embed="site/something" my_var="{segment_1}"}
	{/exp:channel:entries}
	{/exp:pre_embed}

	{!--embed--}
	<p>{title}: {your_custom_field} {embed:my_var}</p>


### Parsing variables
To parse some variables in the template set the extra parameter

	{exp:pre_embed parse="inward" extra="segment|globals|member"}

**segment**  
will replace {segment_n} variables in the pre-embedded template

**globals**  
Parse all globals (overhead, avoid, rarely needed).

**member**
Parse 'logged_in_member_id', 'logged_in_group_id', 'logged_in_member_group', 'logged_in_username', 'logged_in_screen_name'


### Tada!
Now you can re-use the same embed more easily.