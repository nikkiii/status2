		
			<hr>
			<footer class="footer">
		        <p class="pull-right"><a href="#">Back to top</a></p>
		       	<p>&copy; 2012 Nikkii.us - <a href="http://github.com/nikkiii/status2">Powered by Status v{$version}</a></p>
	      	</footer>
		</div> <!-- /container -->
		
		<!-- Le javascript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		{if isset($scripts) && is_array($scripts)}
		{foreach $scripts as $url}<script src="{$url}"></script>{/foreach}
		{/if}
	</body>
</html>