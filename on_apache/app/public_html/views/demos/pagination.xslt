<?xml version="1.0" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />
<xsl:import href="../banshee/pagination.xslt" />

<xsl:template match="content">
<h1>Pagination</h1>
<table class="table table-striped table-condensed">
<thead>
<tr><th>List items</th></tr>
</thead>
<tbody>
<xsl:for-each select="items/item">
<tr><td><xsl:value-of select="." /></td></tr>
</xsl:for-each>
</tbody>
</table>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>

<div class="btn-group left">
<a href="/demos" class="btn btn-default">Back</a>
</div>
</xsl:template>

</xsl:stylesheet>
