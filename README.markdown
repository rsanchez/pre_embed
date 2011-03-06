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
		{pre_embed="site/something"}
	{/exp:channel:entries}
	{exp:pre_embed parse="embed"}

	{!--embed--}
	<p>{title}: {your_custom_field}</p>

### Tada!
Now you can re-use the same embed more easily.