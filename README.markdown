# Pre Embed #

Embed a template before other tag parsing, so you can re-use templates more easily.

## Installation

* Copy the /system/expressionengine/third_party/pre_embed/ folder to your /system/expressionengine/third_party/ folder
* Install the extension

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
	{exp:channel:entries channel="your_channel"}
		{pre_embed="site/something" my_var="{segment_1}"}
	{/exp:channel:entries}

	{!--embed--}
	<p>{title}: {your_custom_field} {embed:my_var}</p>


## Updating from versions prior to 1.1.0

Pre Embed is now an extension and no longer requires a "wrapping" tag pair. You must update your templates. For instance, you'd change this:

	{exp:pre_embed parse="inward"}
	{exp:channel:entries channel="your_channel"}
		{pre_embed="site/something"}
	{/exp:channel:entries}
	{/exp:pre_embed}

To the much simpler this:

	{exp:channel:entries channel="your_channel"}
		{pre_embed="site/something"}
	{/exp:channel:entries}	