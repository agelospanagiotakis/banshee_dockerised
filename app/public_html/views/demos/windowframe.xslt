<?xml version="1.0" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Dialog demo</h1>
<div class="btn-group">
<button class="btn btn-default form_dialog">Open form dialog</button>
<button class="btn btn-default message_dialog">Open message dialog</button>
</div>

<form style="display:none">
<label>Some input:</label>
<input type="text" class="form-control" />
</form>
</xsl:template>

</xsl:stylesheet>
