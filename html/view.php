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

    $HOST = $_ENV["DB_HOST"];
    $USERNAME = $_ENV["DB_USERNAME"];
    $PASSWORD = $_ENV["DB_PASSWORD"];
    $DB = $_ENV["DB_NAME"];
    $PORT = $_ENV["DB_PORT"];
    $TABLE = $_ENV["DB_TABLE"];
    
    $con = mysqli_connect($HOST, $USERNAME, $PASSWORD, $DB, $PORT);
    
    $guid = $_GET['guid'];
    $guid_safe = mysqli_real_escape_string($con, $guid);
    
    $tableSQL = "SHOW TABLES LIKE '$TABLE';";
    $tables = $con->query($tableSQL);

    if ($tables->num_rows < 1) {
        die("No data found. Please try again after sharing your first code. (ERR_TABLE_NOT_CREATED_YET)");
    }

    $selectSQL = "SELECT * FROM `$TABLE` WHERE `guid`='$guid_safe';";
    $result = $con->query($selectSQL);
    
    if ($result->num_rows == 0) {
        die("The specified Code-Snippet was not found. <a style='color: white;' href='./index.php'>Home</a>");
    }
    
    $row = $result->fetch_assoc();
    
    $GLOBALS['content'] = htmlspecialchars($row["Content"]);
    $GLOBALS['title'] = htmlspecialchars($row["Title"]);
    $GLOBALS['language'] = htmlspecialchars($row["Language"]);
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

    <div class="d-flex justify-content-between mb-2">
        <h2><span><?php echo $GLOBALS['title']; ?></span> (<span id="language"><?php echo $GLOBALS['language']; ?></span>)</h2>
        <button type="button" class="btn btn-dark" id="copyBtn" onclick="copyCodeToClipboard();">Copy</button>
    </div>

    <div id="editor-div">
        <pre id="editor" class="acrylic"><?php echo $GLOBALS['content']; ?></pre>
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
            readOnly: true
        });

        const modes = ace.require("ace/ext/modelist").modes;

        editor.getSession().on("changeMode", () => {
            var mode = editor.getSession().getMode().$id;
            var caption = modes.find(obj => obj.mode === mode).caption;
            document.getElementById("language").innerText = caption;
        });

        const mode = "<?php echo $GLOBALS['language'] ?>";
        editor.session.setMode(mode);

        function copyCodeToClipboard() {
            copyToClipboard(editor.getValue());

            const copyBtn = document.getElementById("copyBtn");
            const copyBtnDisplay = copyBtn.style.display;

            copyBtn.style.display = "none";
            setTimeout(() => {
                copyBtn.style.display = copyBtnDisplay;
            }, 6000);
        }

        async function copyToClipboard(code) {
            if (navigator.clipboard?.writeText) {
                try {
                    await navigator.clipboard.writeText(code);
                    showToast("Copied to clipboard.");
                } catch(e) {
                    showToast("Code could not be copied: " + e, "Error");
                }
            }
        }
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
    <?php
    $result->free();
    ?>
</body>
</html>