function ckeditor_start(selector) {
	$('textarea' + selector).after('<div id="editor-counters"></div>');

	ClassicEditor
		.create(document.querySelector(selector), {
			toolbar: {
				items:["heading","|","fontFamily","fontSize","fontColor","|","bold","italic","underline","strikethrough","subscript","superscript","|","bulletedList","numberedList","todoList","|","alignment","outdent","indent","-","findAndReplace","undo","redo","|","link","specialCharacters","mediaEmbed","blockQuote","insertTable","code","codeBlock","|","sourceEditing"],
				shouldNotGroupWhenFull: true
			}
		}).then(editor => {
			const wordCountPlugin = editor.plugins.get('WordCount');
			const wordCountWrapper = document.getElementById('editor-counters');
			wordCountWrapper.appendChild( wordCountPlugin.wordCountContainer );

			if (typeof ckeditor_started == "function") {
				ckeditor_started(editor);
			}
		}).catch(error => {
			console.error(error);
		});
}

function ckeditor_add_insert_image_button(callback) {
	var icon = '<svg class="ck ck-icon ck-button__icon" viewBox="0 0 20 20"><path d="M6.91 10.54c.26-.23.64-.21.88.03l3.36 3.14 2.23-2.06a.64.64 0 0 1 .87 0l2.52 2.97V4.5H3.2v10.12l3.71-4.08zm10.27-7.51c.6 0 1.09.47 1.09 1.05v11.84c0 .59-.49 1.06-1.09 1.06H2.79c-.6 0-1.09-.47-1.09-1.06V4.08c0-.58.49-1.05 1.1-1.05h14.38zm-5.22 5.56a1.96 1.96 0 1 1 3.4-1.96 1.96 1.96 0 0 1-3.4 1.96z"></path></svg>';
	var button = '<button class="ck ck-button ck-off" type="button" data-cke-tooltip-text="Insert image" tabindex="-1" aria-labelledby="ck-editor__aria-label_e1ef8259b9c8dfde5e02286d9851b0ae5" onClick="javascript:' + callback + '()">' + icon + '</button>';
	$('div.ck-toolbar__items div:nth-of-type(9)').after(button);
}
