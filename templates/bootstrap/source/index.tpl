{include file="header.tpl"}
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#all" data-toggle="tab">All</a>
				</li>
{foreach $providers as $provider}{if !empty($provider->servers)}
				<li><a href="#{$provider->shortname}" data-toggle="tab">{$provider->name}</a></li>
{/if}{/foreach}
			</ul>
			<div class="tab-content">
				<div id="all" class="tab-pane active">
				</div>
{foreach $providers as $provider}{if !empty($provider->servers)}
				<div id="{$provider->shortname}" class="tab-pane active">
					<h3>{$provider->name}</h3>
					<table class="table table-bordered table-hover table-center">
						<thead>
							<tr>
								<th class="span2" scope="col">Name</th>
								<th class="span2" scope="col">Uptime</th>
								<th class="span3" scope="col">RAM</th>
								<th class="span3" scope="col">Disk</th>
								<th class="span2" scope="col">Load</th>
								<th class="span2" scope="col">Network</th>
								<th class="span2" scope="col">Last Updated</th>
							</tr>
						</thead>
						<tbody>
{foreach $provider->servers as $server}
{assign var="memperc" value=$server->memused/$server->memtotal*100}
{assign var="diskperc" value=$server->diskused/$server->disktotal*100}
							<tr style="text-align: center">
								<td>{$server->name}</td>
								<td>{$server->uptime}</td>
								<td>
									{$server->memused}MB/{$server->memtotal}MB
									<div class="custombar progress {progressClass($memperc)}">
										<div class="bar" style="width: {$memperc}%;"></div>
									</div>
								</td>
								<td>
									{$server->diskused}GB/{$server->disktotal}GB
									<div class="custombar progress {progressClass($diskperc)}">
										<div class="bar" style="width: {$diskperc}%;"></div>
									</div>
								</td>
								<td>
									<span class="label label-success">{number_format($server->load1, 2)}</span>
									<span class="label label-success">{number_format($server->load5, 2)}</span>
									<span class="label label-success">{number_format($server->load15, 2)}</span>
								</td>
								<td>
{if !empty($server->netin) && !empty($server->netout)}
									In: {$server->netin}/s<br />
									Out: {$server->netout}/s
{else}
									Unknown
{/if}
								</td>
								<td><span class="countup" data-time="{$server->time}">{sec_human(time() - $server->time)}</span></td>
							</tr>
{/foreach}
						</tbody>
					</table>
				</div>
{/if}{/foreach}
			</div>
{include file="footer.tpl"}