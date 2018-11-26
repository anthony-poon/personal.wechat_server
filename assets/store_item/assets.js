import axios from 'axios';

$(document).ready(function() {
    $(document).on("click", ".delete-btn", function(){
        let url = $(this).data("ajax-url");
        let method = $(this).data("ajax-method");
        axios({
            method: method,
            url: url,
        }).then(function(response){
            location.reload()
        }).catch(function (response) {
            console.log(response);
        })
    });

    $(document).on("click", "#upload-btn", function(){
        $("#upload-file").click();
    });

    $(document).on("change", "#upload-file", function(evt){
        let url = $(this).data("ajax-url");
        let method = $(this).data("ajax-method");
        let file = new FormData();
        file.append("image", evt.target.files[0]);
        axios({
            method: method,
            url: url,
            data: file
        }).then(function(response){
            //location.reload()
        }).catch(function (response) {
            console.log(response);
        })
    })
});