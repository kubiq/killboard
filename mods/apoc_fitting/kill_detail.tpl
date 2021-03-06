{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
<table cellpadding=0 cellspacing=1 border=0>
    <tr>
        <td width=360 align=left valign=top><table class=kb-table width=360 cellpadding=0 cellspacing=1 border=0>
                <tr class= {cycle name=ccl}>
                    <td rowspan=3 width="64"><img src="{$VictimPortrait}" border="0" width="64" height="64" alt="victim"></td>
                    <td class=kb-table-cell width=64><b>Victim:</b></td>
                    <td class=kb-table-cell><b><a href="{$VictimURL}">{$VictimName}</a></b></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell width=64><b>Corp:</b></td>
                    <td class=kb-table-cell><b><a href="{$VictimCorpURL}">{$VictimCorpName}</a></b></td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell width=64><b>Alliance:</b></td>
                    <td class=kb-table-cell><b><a href="{$VictimAllianceURL}">{$VictimAllianceName}</a></b></td>
                </tr>
            </table>

            <!--MapMod -->
            {if $loc_active}
            {if $config->get('map_mod_killdet_active') }
            <br />
            <div class="block-header">Location</div>
                      <table class="kb-table" border="0" cellspacing="1" width="360">
            <tr><td align="center">
            <img src="map.php?mode=sys&sys_id={$SystemID}&size=300" border="0" alt="map">
            <br />
            </td></tr></table>
            <br />
            {/if}
            {/if}
            <!--End MapMod -->

            <div class=block-header>Involved parties:
            {if $config->get('killlist_involved')}
                {$InvolvedPartyCount}
            {/if}
            </div>

            {if $showext && $InvolvedPartyCount > 4}
            <table class=kb_table_involved width=360 border=0 cellspacing="1">
                <tr class=kb-table-header>
                    {if $AlliesCount > 1 || !$kill}<th>Alliances</th> {/if}<th>Corporations</th> <th>Ships</th>
                </tr>

                {assign var="first" value="true"}

                {foreach from=$InvAllies key=key item=l}
                    <tr class=kb-table-row-even>
                        {if $AlliesCount > 1 || !$kill}
                        <td class=kb-table-cell>
                            ({$l.quantity}) {$key|truncate:30:"...":true} <br/>
                        </td>
                        {/if}
                        <td class=kb-table-cell>
                            {if $AlliesCount > 1 || !$kill}
                                {foreach from=$l.corps key=key1 item=l1}
                                    ({$l1}) {$key1|truncate:21:"...":true} <br/>
                                {/foreach}
                            {else}
                                {foreach from=$l.corps key=key1 item=l1}
                                    ({$l1}) {$key1|truncate:35:"...":true} <br/>
                                {/foreach}
                            {/if}
                        </td>
                        {if $first == "true"}
                            <td rowspan={$AlliesCount} class=kb-table-cell NOWRAP>
                            {if $AlliesCount > 1 || !$kill}
                                {foreach from=$InvShips key=key item=l}
                                    ({$l}) {$key|truncate:16:"...":true} <br/>
                                {/foreach}
                            {else}
                                {foreach from=$InvShips key=key item=l}
                                    ({$l}) {$key|truncate:22:"...":true} <br/>
                                {/foreach}
                            {/if}
                           </td>

                            {assign var="first" value="false"}
                        {/if}
                    </tr>
                {/foreach}

            </table>
            <br/>
            {/if}
            <table class=kb-table width=360 border=0 cellspacing="1">

                {foreach from=$involved key=key item=i}
                    {if $IsAlly eq true}
                        <tr class={cycle name=ccl}>
	                        <td rowspan=5 width="64"><img {if $i.FB == "true"}class=finalblow{/if} height="64" width="64" src="{$i.portrait}" border="0" alt="inv portrait"></td>
	                        <td rowspan=5 width="64"><img {if $i.FB == "true"}class=finalblow{/if} height="64" width="64" src="{$i.shipImage}" border="0" alt="{$i.ShipName}"></td>

		                    <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.PilotURL}">{$i.PilotName}</a></td>
                        </tr>
                        <tr class={cycle name=ccl}>
                           {if $AllyCorps[$i.CorpName] eq ""}
                                <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.CorpURL}">{$i.CorpName}</a></td>
                           {else}
                                <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.CorpURL}">{$i.CorpName}</a></td>
                           {/if}
                        </tr>
                        <tr class={cycle name=ccl}>
                            {if $i.AlliName eq $HomeName}
                                <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.AlliURL}">{$i.AlliName}</a></td>
                            {else}
                                <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.AlliURL}">{$i.AlliName}</a></td>
                            {/if}
                        </tr>
                    {else}
                        <tr class={cycle name=ccl}>
                            <td rowspan=5 width="64"><img {if $i.FB == "true"}class=finalblow{/if} height="64" width="64" src="{$i.portrait}" border="0"></td>
                            <td rowspan=5 width="64"><img {if $i.FB == "true"}class=finalblow{/if} height="64" width="64" src="{$i.shipImage}" border="0"></td>

                            {if $i.CorpName eq $HomeName}
                                <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px; background-color: #707000;"><a href="{$i.PilotURL}">{$i.PilotName}</a></td>
                            {else}
                                <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.PilotURL}">{$i.PilotName}</a></td>
                            {/if}
                        </tr>
                        <tr class={cycle name=ccl}>
                            <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.CorpURL}">{$i.CorpName}</a></td>
                        </tr>
                        <tr class={cycle name=ccl}>
                            <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><a href="{$i.AlliURL}">{$i.AlliName}</a></td>
                        </tr>
                    {/if}

                    <tr class={cycle name=ccl}>
                        <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;"><b><a href="?a=invtype&amp;id={$i.ShipID}">{$i.ShipName}</a></b> ({$i.shipClass})</td>
                    </tr>
                    <tr class={cycle name=ccl}>
                        <td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;">{if $i.weaponID}<a href="?a=invtype&amp;id={$i.weaponID}">{$i.weaponName}</a>{else}{$i.weaponName}{/if}</td>
                    </tr>
                    <tr class={cycle name=ccl}>
                        <td colspan=2 class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;">Damage done:</td><td class=kb-table-cell style="padding-top: 1px; padding-bottom: 1px;">{$i.damageDone|number_format} {if $VictimDamageTaken > 0}({$i.damageDone/$VictimDamageTaken*100|number_format}%){/if}</td>
                    </tr>
                {/foreach}

            </table>
{if $config->get('comments')}{$comments}{/if}
        </td>
        <td width=50>&nbsp;</td>
        <td align=left valign=top width=398><table class=kb-table width=398 cellspacing="1">
                <tr class={cycle name=ccl}>
                    <td width="64" height="64" rowspan=3><img src="{$VictimShipImg}" width="64" height="64" alt="{$ShipName}"></td>
                    <td class=kb-table-cell><b>Ship:</b></td>
                    <td class=kb-table-cell><b><a href="?a=invtype&amp;id={$ShipID}">{$ShipName}</a></b> ({$ClassName})</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell><b>Location:</b></td>
                    <td class=kb-table-cell><b><a href="{$SystemURL}">{$System}</a></b> ({$SystemSecurity})</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td class=kb-table-cell><b>Date:</b></td>
                    <td class=kb-table-cell>{$TimeStamp}</td>
                </tr>
                {if $showiskd}
                <tr class={cycle name=ccl}>
                    <td colspan=2 class=kb-table-cell><b>Total ISK Loss:</b></td>
                    <td class=kb-table-cell>{$TotalLoss}</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td colspan=2 class=kb-table-cell><b>Total Damage Taken:</b></td>
                    <td class=kb-table-cell>{$VictimDamageTaken|number_format}</td>
                </tr>
                {/if}
            </table>

          <br />
          <div id="fitting" style="position:relative; height:398px; width:398px;" title="fitting">
		<div id="mask" style="position:absolute; left:0px; top:0px; width:398px; height:398px; z-index:0;">
			<img border="0" style="position:absolute; height='398' width='398' filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(
     			src='{$img_url}/{$themedir}/{$panel_colour}.png', sizingMethod='image');" src='{$img_url}/{$themedir}/{$panel_colour}.png' alt=''></div>

		<div id="high1" style="position:absolute; left:73px; top:90px; width:32px; height:32px; z-index:1;">{$fitting_high.0.Icon}</div>
		<div id="high2" style="position:absolute; left:100px; top:67px; width:32px; height:32px; z-index:1;">{$fitting_high.1.Icon}</div>
    <div id="high3" style="position:absolute; left:133px; top:50px; width:32px; height:32px; z-index:1;">{$fitting_high.2.Icon}</div>
    <div id="high4" style="position:absolute; left:167px; top:41px; width:32px; height:32px; z-index:1;">{$fitting_high.3.Icon}</div>
    <div id="high5" style="position:absolute; left:202px; top:41px; width:32px; height:32px; z-index:1;">{$fitting_high.4.Icon}</div>
    <div id="high6" style="position:absolute; left:236px; top:50px; width:32px; height:32px; z-index:1;">{$fitting_high.5.Icon}</div>
    <div id="high7" style="position:absolute; left:270px; top:65px; width:32px; height:32px; z-index:1;">{$fitting_high.6.Icon}</div>
    <div id="high8" style="position:absolute; left:295px; top:89px; width:32px; height:32px; z-index:1;">{$fitting_high.7.Icon}</div>

		<div id="mid1" style="position:absolute; left:48px; top:133px; width:32px; height:32px; z-index:1;">{$fitting_med.0.Icon}</div>
	  <div id="mid2" style="position:absolute; left:40px; top:168px; width:32px; height:32px; z-index:1;">{$fitting_med.1.Icon}</div>
		<div id="mid3" style="position:absolute; left:40px; top:203px; width:32px; height:32px; z-index:1;">{$fitting_med.2.Icon}</div>
		<div id="mid4" style="position:absolute; left:50px; top:237px; width:32px; height:32px; z-index:1;">{$fitting_med.3.Icon}</div>
		<div id="mid5" style="position:absolute; left:66px; top:267px; width:32px; height:32px; z-index:1;">{$fitting_med.4.Icon}</div>
		<div id="mid6" style="position:absolute; left:91px; top:292px; width:32px; height:32px; z-index:1;">{$fitting_med.5.Icon}</div>
		<div id="mid7" style="position:absolute; left:123px; top:313px; width:32px; height:32px; z-index:1;">{$fitting_med.6.Icon}</div>
		<div id="mid8" style="position:absolute; left:155px; top:326px; width:32px; height:32px; z-index:1;">{$fitting_med.7.Icon}</div>

    <div id="low1" style="position:absolute; left:313px; top:133px; width:32px; height:32px; z-index:1;">{$fitting_low.0.Icon}</div>
    <div id="low2" style="position:absolute; left:325px; top:170px; width:32px; height:32px; z-index:1;">{$fitting_low.1.Icon}</div>
    <div id="low3" style="position:absolute; left:325px; top:205px; width:32px; height:32px; z-index:1;">{$fitting_low.2.Icon}</div>
    <div id="low4" style="position:absolute; left:316px; top:239px; width:32px; height:32px; z-index:1;">{$fitting_low.3.Icon}</div>
    <div id="low5" style="position:absolute; left:298px; top:271px; width:32px; height:32px; z-index:1;">{$fitting_low.4.Icon}</div>
    <div id="low6" style="position:absolute; left:276px; top:296px; width:32px; height:32px; z-index:1;">{$fitting_low.5.Icon}</div>
    <div id="low7" style="position:absolute; left:248px; top:315px; width:32px; height:32px; z-index:1;">{$fitting_low.6.Icon}</div>
    <div id="low8" style="position:absolute; left:211px; top:326px; width:32px; height:32px; z-index:1;">{$fitting_low.7.Icon}</div>

    <div id="rig1" style="position:absolute; left:185px; top:110px; width:32px; height:32px; z-index:1;">{$fitting_rig.0.Icon}</div>
    <div id="rig2" style="position:absolute; left:160px; top:160px; width:32px; height:32px; z-index:1;">{$fitting_rig.1.Icon}</div>
    <div id="rig3" style="position:absolute; left:208px; top:160px; width:32px; height:32px; z-index:1;">{$fitting_rig.2.Icon}</div>

    <div id="sub1" style="position:absolute; left:119px; top:214px; width:32px; height:32px; z-index:1;">{$fitting_sub.0.Icon}</div>
    <div id="sub2" style="position:absolute; left:145px; top:245px; width:32px; height:32px; z-index:1;">{$fitting_sub.1.Icon}</div>
    <div id="sub3" style="position:absolute; left:185px; top:257px; width:32px; height:32px; z-index:1;">{$fitting_sub.2.Icon}</div>
    <div id="sub4" style="position:absolute; left:224px; top:244px; width:32px; height:32px; z-index:1;">{$fitting_sub.3.Icon}</div>
    <div id="sub5" style="position:absolute; left:250px; top:215px; width:32px; height:32px; z-index:1;">{$fitting_sub.4.Icon}</div>

    {if $showammo}
    <div id="high1l" style="position:absolute; left:98px; top:114px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.0.type}</div>
    <div id="high2l" style="position:absolute; left:120px; top:95px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.1.type}</div>
    <div id="high3l" style="position:absolute; left:146px; top:82px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.2.type}</div>
    <div id="high4l" style="position:absolute; left:174px; top:76px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.3.type}</div>
    <div id="high5l" style="position:absolute; left:202px; top:76px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.4.type}</div>
    <div id="high6l" style="position:absolute; left:230px; top:83px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.5.type}</div>
    <div id="high7l" style="position:absolute; left:254px; top:97px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.6.type}</div>
    <div id="high8l" style="position:absolute; left:275px; top:116px; width:24px; height:24px; z-index:2;">{$fitting_ammo_high.7.type}</div>

    <div id="mid1l" style="position:absolute; left:75px; top:146px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.0.type}</div>
    <div id="mid2l" style="position:absolute; left:70px; top:174px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.1.type}</div>
    <div id="mid3l" style="position:absolute; left:70px; top:202px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.2.type}</div>
    <div id="mid4l" style="position:absolute; left:78px; top:230px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.3.type}</div>
    <div id="mid5l" style="position:absolute; left:94px; top:256px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.4.type}</div>
    <div id="mid6l" style="position:absolute; left:112px; top:276px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.5.type}</div>
    <div id="mid7l" style="position:absolute; left:136px; top:291px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.6.type}</div>
    <div id="mid8l" style="position:absolute; left:164px; top:301px; width:24px; height:24px; z-index:2;">{$fitting_ammo_mid.7.type}</div>
    {/if}

    </div>

          	<div class="block-header">Ship details</div>
            <table class="kb-table" width="398" border="0" cellspacing="1">
{foreach from=$slots item=slot key=slotindex}
{* set to true to show empty slots *}
{if $destroyed.$slotindex or $dropped.$slotindex}
                <tr class="kb-table-row-even">
                    <td class="item-icon" width="32"><img width="32" height="32" src="{$img_url}/{$slot.img}" alt="{$slot.text}" border="0"></td>
                    <td colspan="2" class="kb-table-cell"><b>{$slot.text}</b> </td>
    {if $config->get('item_values')}
                    <td align="center" class="kb-table-cell"><b>Value</b></td>
    {/if}
                </tr>
    {foreach from=$destroyed.$slotindex item=i}
                <tr class="kb-table-row-odd">
                    <td class="item-icon" width="32" height="34" valign="top"><a href="?a=invtype&amp;id={$i.itemID}">{$i.Icon}</a></td>
                    <td class="kb-table-cell">{$i.Name}</td>
                    <td width="30" align="center">{$i.Quantity}</td>
        {if $config->get('item_values')}
                    <td align="center">{$i.Value}</td>
        {/if}
                </tr>
        {if $admin and $config->get('item_values') and !$fixSlot}
                    <tr class="kb-table-row-even">
                      <td height="34" colspan="4" valign="top" align="right"><form method="post" action=""><table><tr>
                        <td>
                            <div align="right">
                                Current single Item Value:
                                <input name="IID" value="{$i.itemID}" type="hidden">
                                <input name="{$i.itemID}" type="text" class="comment-button" value="{$i.single_unit}" size="6">
                            </div></td>
                        <td height="34" valign="top"><input type="submit" name="submit" value="UpdateValue" class="comment-button"></td>
                      </tr></table></form></td>
                    </tr>
        {/if}
        {if $admin and $i.slotID < 4 and $fixSlot}
                    <tr class="kb-table-row-even">
                      <form method="post" action="">
                        <td height="34" colspan="3" valign="top">
                            <div align="right">
                                Fix slot:
                                <input name="IID" value="{$i.itemID}" type="hidden">
                                <input name="KID" value="{$KillId}" type="hidden">
								<input name="TYPE" value="destroyed" type="hidden">
								<input name="OLDSLOT" value="{$i.slotID}" type="hidden">
                                <input name="{$i.itemID}" type="text" class="comment-button" value="{$i.slotID}" size="6">
                            </div>
                        <td height="34" valign="top"><input type="submit" name="submit" value="UpdateSlot" class="comment-button"></td>
                      </form>
                    </tr>
        {/if}
    {/foreach}
    {foreach from=$dropped.$slotindex item=i}
                <tr class="kb-table-row-odd" style="background-color: {$dropped_colour};">
                    <td style="border: 1px solid green;" width="32" height="34" valign="top"><a href="?a=invtype&amp;id={$i.itemID}">{$i.Icon}</a></td>
                    <td class="kb-table-cell">{$i.Name}</td>
                    <td width="30" align="center">{$i.Quantity}</td>
        {if $config->get('item_values')}
                    <td align="center">{$i.Value}</td>
        {/if}
                </tr>
        {if $admin and $config->get('item_values') and !$fixSlot}
                    <tr class="kb-table-row-even">
                      <td height="34" colspan="4" valign="top" align="right"><form method="post" action=""><table><tr>
                        <td>
                            <div align="right">
                                Current single Item Value:
                                <input name="IID" value="{$i.itemID}" type="hidden">
                                <input name="{$i.itemID}" type="text" class="comment-button" value="{$i.single_unit}" size="8">
                            </div></td>
                        <td height="34" valign="top"><input type="submit" name="submit" value="UpdateValue" class="comment-button"></td>
                      </tr></table></form></td>
                    </tr>
        {/if}
	{if $admin and $i.slotID < 4 and $fixSlot}
                    <tr class="kb-table-row-even">
                      <form method="post" action="">
                        <td height="34" colspan="3" valign="top">
                            <div align="right">
                                Fix slot:
                                <input name="IID" value="{$i.itemID}" type="hidden">
                                <input name="KID" value="{$KillId}" type="hidden">
								<input name="TYPE" value="dropped" type="hidden">
								<input name="OLDSLOT" value="{$i.slotID}" type="hidden">
                                <input name="{$i.itemID}" type="text" class="comment-button" value="{$i.slotID}" size="6">
                            </div>
                        <td height="34" valign="top"><input type="submit" name="submit" value="UpdateSlot" class="comment-button"></td>
                      </form>
                    </tr>
        {/if}
    {/foreach}
{/if}
{/foreach}
{if $item_values}
                <tr class={cycle name=ccl}>
                    <td align="right" colspan="3"><b>Damage taken:</b></td>
                    <td align="right">{$VictimDamageTaken|number_format}</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td colspan="3"><div align="right"><strong>Total Module Loss:</strong></div></td>
                    <td align="right">{$ItemValue}</td>
                </tr>
                <tr class={cycle name=ccl} style="background-color: {$dropped_colour};">
                    <td style="border: 1px solid {$dropped_colour};" colspan="3"><div align="right"><strong>Total Module Drop:</strong></div></td>
                    <td style="border: 1px solid green;" align="right">{$DropValue}</td>
                </tr>
                <tr class={cycle name=ccl}>
                    <td colspan="3"><div align="right"><strong>Ship Loss:</strong></div></td>
                    <td align="right">{$ShipValue}</td>
                </tr>
        {if $admin and $config->get('item_values') and !$fixSlot}
                    <tr class="kb-table-row-even">
                      <td height="34" colspan="4" valign="top" align="right"><form method="post" action=""><table><tr>
                        <td>
                            <div align="right">
                                Current Ship Value:
                                <input name="SID" value="{$Ship->getID()}" type="hidden">
                                <input name="{$Ship->getID()}" type="text" class="comment-button" value="{$Ship->getPrice()}" size="10">
                            </div></td>
                        <td height="34" valign="top"><input type="submit" name="submit" value="UpdateValue" class="comment-button"></td>
                      </tr></table></form></td>
                    </tr>
        {/if}
                <tr class={cycle name=ccl} style="background-color: #600000;">
                    <td style="border: 1px solid #600000;" colspan="3"><div align="right"><strong>Total Loss:</strong></div></td>
                    <td style="border: 1px solid #C00000;" align="right">{$TotalLoss}</td>
                </tr>
{/if}
            </table>
        </td>
    </tr>
</table>