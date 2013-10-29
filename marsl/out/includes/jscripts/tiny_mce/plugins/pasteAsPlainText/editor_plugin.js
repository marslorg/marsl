/**
 *    Will automatically convert pasted text so no junk code will be included.
 *    This plugin depends on the core paste plugin.
 **/
(function() {
	tinymce.create('tinymce.plugins.pasteAsPlainTextPlugin', {
		init : function(editor, url) {
			// We force the pasting to occur as plain text
			editor.pasteAsPlainText = true;
			// We also put it as sticky to allow for multiple pastings
			editor.settings.paste_text_sticky = true;

			// Adding some special post process
			editor.settings.paste_postprocess = function(a, o) {
				var text = o.content,
					split = String.fromCharCode(13)+String.fromCharCode(10)
				;

				// If content is long text without HTML, We'll break it into <p>ieces
				if (text.charAt(0)!='<' && text.indexOf(split)!=-1) {
					// Adding <p> around each line
					var node = document.createElement('div'),
						sentences = text.split(split)
					;
					for(var i=0,max=sentences.length;i!=max;i++) {
						node.innerHTML+='<br />'+sentences[i];
					}
					node.innerHTML = node.innerHTML.replace(/__MCE_ITEM__/g, '');

					// Saving back in original content/node
					o.node = node;
					o.content = node.innerHTML;
				}
				return o;
			}
		}
	});

	// Register plugin
	tinymce.PluginManager.add('pasteAsPlainText', tinymce.plugins.pasteAsPlainTextPlugin);
})();