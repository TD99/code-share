<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Code-Share</title>
    <link rel="stylesheet" href="./assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto+Slab:300,400|Roboto:300,400,700">
    <link rel="stylesheet" href="./assets/css/edit.css">

    <!-- Apple -->
    <link rel="apple-touch-icon" sizes="180x180" href="./assets/favicon/apple-touch-icon-180x180.png">
    <!-- Browser -->
    <link rel="shortcut icon" type="image/x-icon" href="./assets/favicon/favicon-32x32.ico">
    <link rel="icon" type="image/png" sizes="96x96" href="./assets/favicon/favicon-96x96.png">
    <!-- Windows -->
    <meta name="msapplication-square310x310logo" content="./assets/favicon/mstile-310x310.png">
</head>
<body>
    <?php
        error_reporting(0);
        ini_set('display_errors', 0);
        if (isset($_GET["toosmooth"])) {
            echo "<style>#editor * {transition: all .2s cubic-bezier(0.68, -0.55, 0.27, 1.55) !important;}</style>";
        }
    ?>
    <div class="modal fade" id="bgModal" tabindex="-1" aria-labelledby="bgModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-root acrylic">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Background</h1>
                    <span class="fs-5">&nbsp;|&nbsp;</span>
                    <a href="https://pixabay.com/"><img height="20rem" src="./assets/img/pixabay.png"></a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="form-floating d-flex flex-row mb-2">
                            <input type="text" class="form-control acrylic specinp" placeholder=" " id="bgsearch">
                            <label for="floatingTextarea">Enter your query</label>

                            <button class="btn btn-dark ms-2" onclick="loadBGImages();">Search</button>
                        </div>

                        <div id="images"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" onclick="resetBGImages();">Reset</button>
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="liveToastTitle"></strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="liveToastBody"></div>
        </div>
    </div>

    <header class="d-xl-flex justify-content-between">
        <a class="navbar-brand" href="index.php">
            <span style="font-size: 21px; font-weight: bold;">CodeShare</span>
        </a>
        <span class="interactive-text" data-bs-toggle="modal" data-bs-target="#bgModal">Set Background</span>
        <span>Version 1.0</span>
    </header>

    <div id="options">
        <div class="form-floating">
            <input type="text" class="form-control acrylic specinp" placeholder=" " id="title">
            <label for="floatingTextarea">Title</label>
        </div>
        <div class="form-floating">
            <select id="language" class="form-select acrylic specinp" onchange="updateMode()"></select>
            <label for="language">Language</label>
        </div>
        <button type="button" class="btn btn-acrylic acrylic" id="editorOptBtn" onclick="editor.execCommand('showSettingsMenu');">Options</button>
        <button type="button" class="btn btn-dark" id="SendBtn" onclick="sendData();">Share</button>
    </div>

    <div id="editor-div">
        <pre id="editor" class="acrylic"></pre>
    </div>
        
    <script src="./assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="./assets/js/bs-init.js"></script>

    <script src="./lib/ace-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
    <script src="./lib/ace-min-noconflict/ext-language_tools.js"></script>
    <script src="./lib/ace-min-noconflict/ext-modelist.js"></script>
    <script>
        var editor = ace.edit("editor");
        editor.setTheme("ace/theme/tomorrow_night_bright");
    
        editor.setOptions({
            showPrintMargin: false,
            enableBasicAutocompletion: true,
            enableSnippets: true,
            enableLiveAutocompletion: true
        });

        var modelist = ace.require('ace/ext/modelist');
        modelist.modes.forEach(function(mode) {
            let option = document.createElement('option');
            option.value = mode.mode;
            option.innerText = mode.caption;
            document.getElementById("language").appendChild(option);
        });

        function updateMode() {
            const mode = document.getElementById("language").value;
            editor.getSession().setMode(mode);
        }

        editor.getSession().on("changeMode", () => {
            const mode = editor.getSession().getMode();
            document.getElementById("language").value = mode.$id;
        });

        editor.session.setMode(modelist.modes[0].mode);

        const initOpt = JSON.parse(localStorage.getItem("editor_options"));

        if (initOpt != null) {
            editor.setOptions(initOpt);
        }

        function checkEditorOptions() {
            const currentOpt = JSON.stringify(editor.getOptions());
            const storedOpt = localStorage.getItem("editor_options");

            if (currentOpt == null || currentOpt == undefined) return;

            if (currentOpt !== storedOpt) {
                localStorage.setItem("editor_options", currentOpt);
            }
        }
        setInterval(checkEditorOptions, 10000);

        document.body.addEventListener("drop", (event) => {
            event.preventDefault();
            
            var file = event.dataTransfer.files[0];
            
            var reader = new FileReader();
            reader.onload = (event) => {
                var fileContents = event.target.result;
                
                if (fileContents.length > 500000) {
                    showToast("File is too big! Maximum file size is 500KB.", "Error");
                    return;
                }

                if (isBinary(fileContents)) {
                    showToast("This file appears to be binary and may not be editable.", "Warning");
                }
                
                editor.setValue(fileContents);
                const mode = modelist.getModeForPath(file.name).mode;
                editor.session.setMode(mode);

                showToast(`File '${file.name}' has been imported.`, "Info");
            };
            reader.readAsText(file);
        });
        document.body.addEventListener("dragover", (event) => {
            event.preventDefault();
        });

        function isBinary(str) {
            for (var i = 0; i < str.length; i++) {
                if (str.charCodeAt(i) == 0) {
                return true;
                }
            }
            return false;
        }

        window.addEventListener("beforeunload", function(event) {
            event.returnValue = false;

            showToast("Autosave is not yet implemented. Any unsaved changes will be lost if you leave this page.", "Warning");
        });
    </script>
    <script>
        async function loadBGImages() {
            document.getElementById("images").innerHTML = "";

            const apiKey = "25305443-9311ebf5cf7fbdbc1d7750517";
            const query = encodeURIComponent(document.getElementById("bgsearch").value);
            const limit = 200;
            const safesearch = true;
            const category = "backgrounds";
            
            const url = `https://pixabay.com/api/?key=${apiKey}&q=${query}&safesearch=${safesearch}&per_page=${limit}&category=${category}`;

            const ans = await fetch(url);
            const json = JSON.parse(await ans.text());

            json["hits"].forEach(e => {
                const img = document.createElement("img");
                img.src = e.previewURL;
                img.loading = "lazy";
                img.addEventListener("click", () => {
                    const bgurl = e.largeImageURL;
                    localStorage.setItem("bgurl", bgurl);
                    document.getElementsByTagName("body")[0].style = `background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8) 99%), url('${bgurl}') center / cover;`;
                });
                document.getElementById("images").appendChild(img);
            });
        }

        function resetBGImages() {
            localStorage.removeItem("bgurl");
            document.getElementsByTagName("body")[0].style = "background: black";
        }

        document.addEventListener("DOMContentLoaded", () => {
            const bgurl = localStorage.getItem("bgurl");
            if (bgurl) {
                document.getElementsByTagName("body")[0].style = `background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8) 99%), url('${bgurl}') center / cover;`;
            }
        }, false);

        function showToast(body, title="CodeShare", unsafeMode=false, autoHide=true) {
            const toastE = document.getElementById('liveToast');
            const toastTitleE = document.getElementById('liveToastTitle');
            const toastBodyE = document.getElementById('liveToastBody');

            toastTitleE.textContent = title;
            if (unsafeMode) {
                toastBodyE.innerHTML = body;
            } else {
                toastBodyE.textContent = body;
            }

            const toast = new bootstrap.Toast(toastE, {autohide: autoHide});
            toast.show();
        }
    </script>
    <script src="./lib/jquery-compressed/jquery-3.6.4.min.js"></script>
    <script>
        function sendData() {
            const contentV = editor.getValue();
            const titleV = document.getElementById('title').value;
            const languageV = document.getElementById('language').value;

            $.ajax({
                method: "POST",
                url: "./workers/sendDB.php",
                data: { content: contentV, title: titleV, language: languageV }
            }).done((msg) => {
                showToast(msg, undefined, true, false);
            });
        }
    </script>
</body>
</html>