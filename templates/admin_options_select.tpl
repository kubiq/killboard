{strip}
<tr><td width="160"><b>{$opt.descr}:</b></td><td>
<select id="option[{$opt.name}]" name="option[{$opt.name}]">
{foreach from=$options key=key item=i}
<option value="{$i.value}"{if $i.state} selected="selected"{/if}>{$i.descr}</option>
{/foreach}
</select>
{if $opt.hint}
&nbsp;({$opt.hint})
{/if}
</td></tr>
{/strip}
