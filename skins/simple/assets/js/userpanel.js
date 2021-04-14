var csrfName = $('#csrf_name').val();
var csrfValue = $('#csrf_value').val();
    
    
  Dropzone.options.myDropzone = {
  paramName: "avatar", // The name that will be used to transfer the file
  maxFilesize: 2, // MB 
  dictDefaultMessage: '<i class="fas fa-file-import fa-5x"></i>',
  params: {"csrf_name" : csrfName, "csrf_value" : csrfValue},
  transformFile: function(file, done) {

    var myDropZone = this;

    // Create the image editor overlay
    var editor = document.createElement('div');
    editor.style.position = 'fixed';
    editor.style.left = 0;
    editor.style.right = 0;
    editor.style.top = 0;
    editor.style.bottom = 0;
    editor.style.zIndex = 9999;
    editor.style.backgroundColor = 'rgba(0, 0, 0, 0.9)';
    document.body.appendChild(editor);
    
    var cancle = document.createElement('button');
    cancle.className = 'btn btn-danger';
    cancle.style.position = 'absolute';
    cancle.style.right = '10px';
    cancle.style.top = '10px';
    cancle.style.zIndex = 9999;
    cancle.textContent = '×';
    cancle.addEventListener('click', function() {
      $('.dz-preview').remove();
      $('.dz-message').fadeIn();
      $('.croppie-container').fadeOut();
      
    });
    // Create the confirm button
    var confirm = document.createElement('button');
    confirm.className = 'btn btn-light';
    confirm.style.position = 'absolute';
    confirm.style.left = '10px';
    confirm.style.top = '10px';
    confirm.style.zIndex = 9999;
    confirm.textContent = 'Ok';
    confirm.addEventListener('click', function() {

      // Get the output file data from Croppie
      croppie.result({
        type:'blob',
        size: {
          width: 256,
          height: 256
        }
      }).then(function(blob) {

        // Update the image thumbnail with the new image data
        myDropZone.createThumbnail(
          blob,
          myDropZone.options.thumbnailWidth,
          myDropZone.options.thumbnailHeight,
          myDropZone.options.thumbnailMethod,
          false, 
          function(dataURL) {

            // Update the Dropzone file thumbnail
            myDropZone.emit('thumbnail', file, dataURL);

            // Return modified file to dropzone
            done(blob);
             setTimeout(function () {
                $('#changeAvatar').modal('hide');
                location.reload();               
            }, 2000);   
          }
        );

      });

      // Remove the editor from view
      editor.parentNode.removeChild(editor);
      myDropZone.removeAllFiles();
     

    });
    editor.appendChild(confirm);
    editor.appendChild(cancle)
    // Create the croppie editor
    var croppie = new Croppie(editor, {
      enableResize:  false,
      enableZoom: true,
      viewport: { width: 300, height: 300, type: 'square' }
    });

    // Load the image to Croppie
    croppie.bind({
      url: URL.createObjectURL(file)
    });
    
  }
};  
    