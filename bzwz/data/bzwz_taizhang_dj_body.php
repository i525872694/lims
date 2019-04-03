<?php
echo <<<ETT
<br><br><br><br><br><br>
<div style="width:18cm;margin:0px auto">
	<div style="text-align:center;width:auto;">
	    <h1 style="font-size:1.5em;margin:0;padding:0;">标 准 物 质 登 记 表</h1>
	    <div style="text-align:left;">编号：LNSHJ-CX-22-2012-JL-02</div>
	</div>
<table class="single" style="width:100%;">
	<tr>
		<td width:3.6cm>标准物质名称</td>
		<td width:5.4cm>$v[wz_name]</td>
		<td width:3.6cm>制造商名称</td>
		<td width:5.4cm>$v[manufacturer]</td>
	</tr>
	<tr>
		<td>规格</td>
		<td>$v[consistence]</td>
		<td>编号</td>
		<td>$v[wz_bh]</td>
	</tr>
	<tr>
		<td>有效期</td>
		<td>$v[time_limit]</td>
		<td>接收状态</td>
		<td>全新口</td>
	</tr>
	<tr style="height:4cm">
		<td colspan="4">
			<div style="text-align:left;padding: 0px 0px 120px;">
				随机技术文件:
			</div>
		</td>
	</tr>
	<tr style="height:4cm">
		<td colspan="4">
			<div style="text-align:left;padding: 0px 0px 120px;">
				验收记录（给出技术指标）：
			</div>
			<div style="margin-left:200px;">
				<span>
					验收员：
				</span>
				<span>
					&nbsp;&nbsp;&nbsp;&nbsp;年&nbsp;&nbsp;&nbsp;&nbsp;月&nbsp;&nbsp;&nbsp;&nbsp;日
				</span>
			</div>
		</td>
	</tr>
	<tr style="height:4cm">
		<td colspan="4">
			<div style="text-align:left;padding: 0px 0px 120px;">
				验证∕比对记录：
			</div>
		</td>
	</tr>
	</table>
</div>
<div style="page-break-before:always;"></div>


ETT;
?>