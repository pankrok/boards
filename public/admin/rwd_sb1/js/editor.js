var editor = new Jodit('#editor', {
    "toolbar": false,
	langusage: 'pl',
    autofocus: true,
	height: "auto",
	"defaultMode": "2",
	"buttons": "|,,|,|,|,|,|,undo,redo,|",
	toolbarAdaptive: true,
	allowResizeX: false,
    allowResizeY: true,
});
editor.events.on('beforeGetValueFromEditor', function () {
        return editor.getNativeEditorValue().replace(/\{%[^\}]+%\}/g, function (match) {
                 return match
                                .replace(/&gt;/g,  '>')
                                .replace(/&lt;/g,  '<');
        });
});