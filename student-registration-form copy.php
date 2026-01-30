<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Student Registration Form</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/css/app.min.css">
  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <!-- Custom style CSS -->
  <link rel="stylesheet" href="assets/css/custom.css">
  <style>
    .logo-img {
      height: 40px;
    }

    @media (min-width: 576px) {
      .logo-img {
        height: 60px;
      }
    }
  </style>
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <!-- include navbar form include folder -->
      <?php include 'include/navbar.php'; ?>

      <div class="main-sidebar sidebar-style-2">
        <!-- include asidebar form include folder -->
        <?php include 'include/asidebar.php'; ?>
      </div>
      <!-- Main Content -->
      <div class="main-content">

        <section class="section">
          <!-- breadcrumb -->
          <div class="row bg-title">
            <div class="col-12">
              <div class="card mb-3">
                <div class="card-body py-2 b-0">
                  <div class="d-flex flex-wrap align-items-center justify-content-between">

                    <!-- LEFT SIDE -->
                    <div class="mb-2 mb-md-0">
                      <h5 class="page-title mb-0"> </i> Manage Student (Add)</h5>
                    </div>

                    <!-- RIGHT SIDE -->
                    <div>
                      <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 bg-transparent p-0">
                          <li class="breadcrumb-item">
                            <a href="#"><i class="fas fa-tachometer-alt"></i> Home</a>
                          </li>
                          <li class="breadcrumb-item">
                            <a href="#"><i class="far fa-file"></i> Student</a>
                          </li>
                          <li class="breadcrumb-item active" aria-current="page">
                            <i class="fas fa-list"></i> Add
                          </li>
                        </ol>
                      </nav>
                    </div>

                  </div>

                </div>
              </div>
            </div>
          </div>

          <div class="card p-2 p-md-4">
            <div class="card-body rounded" style="border: 5px solid #0000004c !important ">
              <div class="row ">
                <div class=" col-12 col-lg-10 text-center">
                  <div class="row ">
                    <div class="col-md-2">
                      <div class="d-flex align-items-end justify-content-between justify-content-md-center flex-wrap">

                        <picture>
                          <source media="(min-width: 576px)" srcset="./assets/img/agslogo.png">
                          <img src="./assets/img/agslogo.png" alt="Logo" class="logo-img d-block">
                        </picture>
                        <div
                          class="d-flex d-inline-block flex-wrap-0 flex-wrap-reverse justify-content-center mt-0 mt-md-4 text-right">
                          <label class="mb-0 ps-2 d-inline-block fw-bold text-center">Registration #: </label>
                          <input type="text" style="width: 100px;"
                            class=" form-control form-control-sm d-inline-block border-0 border-bottom rounded-0"
                            placeholder="____________">
                        </div>
                        <div class="mt-0 px-2 d-none d-md-flex align-items-end text-right">
                          <input type="date" value="<?= date('Y-m-d') ?>"
                            class="form-control form-control-sm border-0 border-bottom rounded-0 p-0 text-center"
                            style="width: 100px;">
                        </div>
                      </div>
                    </div>

                    <div class="col-md-10">
                      <h5 class="d-md-none text-center text-nowrap my-2">Amina Girls Degree College</h5>
                      <h2 class="d-none d-md-block text-center text-nowrap m-0">Amina Girls Degree College</h2>
                      <div class="text-center">
                        <span class="">Address: Gailywal 21-MPR lodhran</span>
                        <br>
                        <h6
                          class=" mt-2 rounded bg-primary px-3 py-1 d-inline-block text-white text-center mb-0 font-weight-bold">
                          Application Form For Registration </h6>
                      </div>
                      <div class="text-center my-3">
                        <div
                          class=" border-primary d-flex gap-1 d-inline-block flex-wrap justify-content-around text-center">
                          <div class="mt- px-2 d-flex align-items-end text-right">
                            <label class="mb-0 fw-bold me-2">Section:</label>
                            <select
                              class="form-select form-select-sm d-inline-block border-0 border-bottom rounded-0 p-0 text-center"
                              style="width: 80px;">
                              <option value="">Section</option>
                              <option>Section A</option>
                              <option>Section B</option>
                            </select>
                          </div>
                          <div class="mt- px-2 d-flex align-items-end text-right">
                            <label class="mb-0 fw-bold me-2">Class:</label>
                            <select
                              class="form-select form-select-sm d-inline-block border-0 border-bottom rounded-0 p-0 text-center"
                              style="width: 80px;">
                              <option value="">Select</option>
                              <option>9th</option>
                              <option>10th</option>
                            </select>
                          </div>
                          <div class="mt-0 px-2 d-flex align-items-end text-right">
                            <label class="mb-0 fw-bold me-2">Session:</label>
                            <select
                              class="form-select form-select-sm d-inline-block border-0 border-bottom rounded-0 p-0 text-center"
                              style="width: 80px;">
                              <option value="">Select</option>
                              <option>2024-26</option>
                            </select>
                          </div>
                          <div class="mt-0 px-2 d-flex align-items-end text-right">
                            <label class="mb-0 fw-bold me-2">Group:</label>
                            <select
                              class="form-select form-select-sm d-inline-block border-0 border-bottom rounded-0 p-0 text-center"
                              style="width: 80px;">
                              <option value="">Select</option>
                              <option>Medical</option>
                              <option>ICS</option>
                            </select>
                          </div>

                        </div>
                        <div class="mt-2 ">
                          <ul class="d-flex justify-content-around list-unstyled p-2 rounded border "
                            style="background-color: #2727271e;">
                            <li class="small fw-bold text-uppercase"><b>Math</b> </li>
                            <li class="small fw-bold text-uppercase"><b>Phy</b> </li>
                            <li class="small fw-bold text-uppercase"><b>Chem</b> </li>
                            <li class="small fw-bold text-uppercase"><b>Computer</b></li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-lg-2 text-center">
                  <div class="border p-2 mb-2 rounded bg-light" style="cursor:pointer; position:relative;"
                    onclick="document.getElementById('manualFile').click();" title="Click to upload manually">

                    <video id="webcam" autoplay playsinline class="img-fluid rounded"
                      style="height: 120px; width: 100%; object-fit: cover; display: none;"></video>

                    <img id="photoPreview" src="assets/img/userdummypic.png" class="img-fluid rounded mx-auto"
                      style="height: 120px; width: 100%; object-fit: cover;" alt="Passport Photo">

                    <button type="button" id="captureBtn" class="btn btn-sm btn-danger position-absolute"
                      style="bottom: 10px; left: 50%; transform: translateX(-50%); display: none; z-index: 10;"
                      onclick="event.stopPropagation(); takeSnapshot();">
                      <i class="fa fa-circle"></i>
                    </button>
                  </div>

                  <div class="d-flex justify-content-center gap-2">
                    <input type="file" id="manualFile" accept="image/*" style="display:none;"
                      onchange="previewManualFile(this)">

                    <button type="button" id="startCamBtn" class="btn btn-sm btn-success w-100" onclick="startWebcam()">
                      <i class="fa fa-camera"></i> Open Camera
                    </button>

                    <canvas id="canvas" style="display:none;"></canvas>
                  </div>
                </div>
                <script>
                  // Yeh line zaroori hai taake browser ko pata chale activeStream kya hai
                  let activeStream = null;

                  function previewManualFile(input) {
                    if (input.files && input.files[0]) {
                      const reader = new FileReader();

                      reader.onload = function(e) {
                        // Stop the camera if it is running
                        if (typeof stopWebcam === "function" && activeStream) {
                          stopWebcam();
                        }

                        // Elements ko update karein
                        const webcamElement = document.getElementById('webcam');
                        const previewElement = document.getElementById('photoPreview');
                        const captureBtn = document.getElementById('captureBtn');

                        if (webcamElement) webcamElement.style.display = "none";
                        if (captureBtn) captureBtn.style.display = "none";

                        // Image source update karein aur display karein
                        previewElement.src = e.target.result;
                        previewElement.style.display = "block";
                      };

                      reader.readAsDataURL(input.files[0]);
                    }
                  }

                  // Ensure stopWebcam is defined so it doesn't crash the script
                  function stopWebcam() {
                    if (activeStream) {
                      activeStream.getTracks().forEach(track => track.stop());
                      activeStream = null;
                    }
                  }
                </script>
              </div>
              <div class="row mb-4">
                <div class="col-12">
                  <div class="d-flex justify-content-between align-items-center mt-3 mt-lg-0">
                    <h6 class="text-dark-subtle border-bottom pb-1"> <i class='fas fa-user-circle'></i> Student
                      Information</h6>
                    <p class="text-dark-subtle border-bottom pb-1 d-none d-md-block">
                      نام(اردومیں) ـــــــــــــــــــــــــــــــــــ&nbsp;&nbsp;<i class='fas fa-user-circle'></i>
                    </p>
                  </div>
                  <div class="row">
                    <div class="col-md-3 mt-2"><label>Student Name</label><input type="text" class="form-control"></div>
                    <div class="col-md-3 mt-2"><label>B-Form / CNIC</label><input type="text" class="form-control">
                    </div>
                    <div class="col-md-3 mt-2"><label>Date of Birth</label><input type="date" class="form-control">
                    </div>
                    <div class="col-md-3 mt-2"><label>Gender</label><select class="form-control">
                        <option>Female</option>
                      </select></div>
                    <div class="col-md-2 mt-2"><label>Mother language</label><input type="text" class="form-control">
                    </div>
                    <div class="col-md-2 mt-2"><label>Cast</label><input type="text" class="form-control"></div>
                    <div class="col-md-3 mt-2"><label>Contact #</label><input type="text" class="form-control"></div>
                    <div class="col-md-5 mt-2"><label>Address</label><input type="text" class="form-control"></div>
                  </div>
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="text-dark-subtle border-bottom my-2 py-2"> <i class='fas fa-users'></i> Guardian
                      Information</h6>
                    <p class="text-dark-subtle border-bottom pb-1 d-none d-md-block">
                      والد یا سرپرست کا نام(اردومیں) ـــــــــــــــــــــــــــــــــــ&nbsp;&nbsp;<i
                        class='fas fa-users'></i></p>
                  </div>
                  <div class="row pb-3">
                    <div class="col-md-3 mt-2"><label>Guardian Name</label><input type="text" class="form-control">
                    </div>
                    <div class="col-md-3 mt-2">
                      <label>Relation</label>
                      <select class="form-control">
                        <option>Father</option>
                        <option>Mother</option>
                      </select>
                    </div>
                    <div class="col-md-3 mt-2"><label>Occupation</label><input type="text" class="form-control"></div>
                    <div class="col-md-3 mt-2"><label>Contact# </label><input type="text" class="form-control"></div>
                  </div>
                </div>
              </div>

              <div class="row rounded" style="background-color: rgba(223, 223, 223, 0.758);">
                <div class="col-12 px-3">
                  <h6 class="text-dark-subtle border-bottom pt-2"> <i class='fas fa-university'></i> Previous School
                    Information</h6>
                  <div class="row pb-3">
                    <div class="col-md-5 mt-2"><label>School Name</label><input type="text" class="form-control"></div>
                    <div class="col-md-2 mt-2"><label>Last Class</label><input type="text" class="form-control"></div>
                    <div class="col-md-2 mt-2"><label>Passing </label><input type="text" class="form-control"></div>
                    <div class="col-md-3 mt-2"><label>Board Name</label><input type="text" class="form-control"></div>

                    <div class="col-md-8 mt-3">
                      <div class="form-check d-flex align-items-center gap-2 mt-3">
                        <input class="form-check-input" type="checkbox" id="declareCheck"
                          style="width: 20px; height: 20px; cursor:pointer;">
                        <label class="form-check-label fw-bold " for="declareCheck" style="cursor:pointer;">
                          &nbsp; &nbsp; I declare that the information provided is correct.
                        </label>
                      </div>
                    </div>
                    <div class="col-md-4 text-end">
                      <div style="border-top: 2px solid #000; width: 200px; display: inline-block;" class="mt-5">
                        <p class="text-center fw-bold mb-0">Student Signature</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-12">
                  <h6 class="text-dark-subtle border-bottom pb-1">
                    <i class='fas fa-file-alt'></i> Documents Upload (Camera & Manual)
                  </h6>

                  <div class="row">
                    <div class="col-md-3 mt-2">
                      <label class="fw-bold small">Student B-Form / CNIC</label>
                      <div class="border p-2 mb-1 text-center bg-light" style="height: 100px; overflow: hidden;">
                        <img id="preview_cnic" src="assets/img/elementor.png" class="img-fluid">
                        <video id="video_cnic" autoplay playsinline
                          style="height: 80px; width: 100%; display: none; object-fit: cover;"></video>
                      </div>
                      <div class="input-group input-group-sm">
                        <input type="file" class="form-control" onchange="previewDoc(this, 'preview_cnic')">
                        <button class="btn btn-success" type="button"
                          onclick="startDocCamera('video_cnic', 'preview_cnic', 'canvas_cnic', 'btn_cnic')">
                          <i class="fas fa-camera" id="btn_cnic"></i>
                        </button>
                      </div>
                      <canvas id="canvas_cnic" style="display:none;"></canvas>
                    </div>

                    <div class="col-md-3 mt-2">
                      <label class="fw-bold small">Guardian CNIC(front)</label>
                      <div class="border p-2 mb-1 text-center bg-light" style="height: 100px; overflow: hidden;">
                        <img id="preview_guardian" src="assets/img/elementor.png" class="img-fluid">
                        <video id="video_guardian" autoplay playsinline
                          style="height: 80px; width: 100%; display: none; object-fit: cover;"></video>
                      </div>
                      <div class="input-group input-group-sm">
                        <input type="file" class="form-control" onchange="previewDoc(this, 'preview_guardian')">
                        <button class="btn btn-success" type="button"
                          onclick="startDocCamera('video_guardian', 'preview_guardian', 'canvas_guardian', 'btn_guardian')">
                          <i class="fas fa-camera" id="btn_guardian"></i>
                        </button>
                      </div>
                      <canvas id="canvas_guardian" style="display:none;"></canvas>
                    </div>

                    <div class="col-md-3 mt-2">
                      <label class="fw-bold small">Guardian CNIC(Back)</label>
                      <div class="border p-2 mb-1 text-center bg-light" style="height: 100px; overflow: hidden;">
                        <img id="preview_transcript" src="assets/img/elementor.png" class="img-fluid">
                        <video id="video_transcript" autoplay playsinline
                          style="height: 80px; width: 100%; display: none; object-fit: cover;"></video>
                      </div>
                      <div class="input-group input-group-sm">
                        <input type="file" class="form-control" onchange="previewDoc(this, 'preview_transcript')">
                        <button class="btn btn-success" type="button"
                          onclick="startDocCamera('video_transcript', 'preview_transcript', 'canvas_transcript', 'btn_transcript')">
                          <i class="fas fa-camera" id="btn_transcript"></i>
                        </button>
                      </div>
                      <canvas id="canvas_transcript" style="display:none;"></canvas>
                    </div>

                    <div class="col-md-3 mt-2">
                      <label class="fw-bold small">Result Card / Certificate</label>
                      <div class="border p-2 mb-1 text-center bg-light" style="height: 100px; overflow: hidden;">
                        <img id="preview_birth" src="assets/img/elementor.png" class="img-fluid">
                        <video id="video_birth" autoplay playsinline
                          style="height: 80px; width: 100%; display: none; object-fit: cover;"></video>
                      </div>
                      <div class="input-group input-group-sm">
                        <input type="file" class="form-control" onchange="previewDoc(this, 'preview_birth')">
                        <button class="btn btn-success" type="button"
                          onclick="startDocCamera('video_birth', 'preview_birth', 'canvas_birth', 'btn_birth')">
                          <i class="fas fa-camera" id="btn_birth"></i>
                        </button>
                      </div>
                      <canvas id="canvas_birth" style="display:none;"></canvas>
                    </div>
                  </div>
                </div>
              </div>

              <script>
                let currentStream = null;

                // Function 1: File select preview (Offline/Manual)
                function previewDoc(input, imgId) {
                  if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                      document.getElementById(imgId).src = e.target.result;
                      document.getElementById(imgId).style.display = "inline-block";
                    }
                    reader.readAsDataURL(input.files[0]);
                  }
                }

                // Function 2: Camera Start/Capture Logic
                async function startDocCamera(videoId, imgId, canvasId, iconId) {
                  const video = document.getElementById(videoId);
                  const img = document.getElementById(imgId);
                  const canvas = document.getElementById(canvasId);
                  const icon = document.getElementById(iconId);

                  // Agar camera pehle se chal raha hai to capture kar lo
                  if (video.style.display === "block") {
                    const context = canvas.getContext('2d');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);

                    img.src = canvas.toDataURL('image/png');
                    img.style.display = "inline-block";
                    video.style.display = "none";

                    // Stop stream
                    if (currentStream) {
                      currentStream.getTracks().forEach(track => track.stop());
                    }
                    icon.className = "fas fa-camera";
                  }
                  // Warna camera start karo
                  else {
                    try {
                      currentStream = await navigator.mediaDevices.getUserMedia({
                        video: {
                          facingMode: "environment"
                        }
                      });
                      video.srcObject = currentStream;
                      video.style.display = "block";
                      img.style.display = "none";
                      icon.className = "fas fa-check-circle"; // Icon badal do capture ke liye
                    } catch (err) {
                      alert("Camera not accessible: " + err);
                    }
                  }
                }
              </script>

              <div class="row mt-4">
                <div class="col-12">
                  <h6 class="text-dark-subtle border-bottom pb-1"> <i class='fas fa-briefcase'></i> Other / Office use
                  </h6>
                  <div class="row">
                    <div class="col-md-4 mt-2 border rounded">
                      <div class="card-body p-2">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                          <label class="mb-0 fw-bold ">Disability:</label>
                          <div class="d-flex gap-2">
                            <label class="mb-0 mx-1 "><input type="radio" name="dis"> Yes</label>
                            <label class="mb-0 mx-1"><input type="radio" name="dis"> No</label>
                          </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                          <label class="mb-0 fw-bold ">Hafiz-Quran:</label>
                          <div class="d-flex gap-2">
                            <label class="mb-0 mx-1 "><input type="radio" name="hafiz"> Yes</label>
                            <label class="mb-0 mx-1"><input type="radio" name="hafiz"> No</label>
                          </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-1">
                          <label class="mb-0 fw-bold ">School Transport:</label>
                          <div class="d-flex gap-2">
                            <label class="mb-0 mx-1"><input type="radio" name="tr"> Yes</label>
                            <label class="mb-0 mx-1"><input type="radio" name="tr"> No</label>
                          </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-1">
                          <label class="mb-0 fw-bold ">Select Route:</label>
                          <div class="d-flex gap-2">
                            <select class="form-select form-select-sm border-0 border-bottom rounded-0 p-0"
                              style="width: 130px; background-color: transparent;">
                              <option value="">Pik & Drop Point</option>
                              <option>Point-2</option>
                              <option>Gailywal Coaster</option>
                            </select>
                          </div>
                        </div>
                        <div class="mt-1">
                          <label class="d-block fw-bold mx-1">Interests:</label>
                          <div class="d-flex flex-wrap gap-2 ">
                            <label class="mx-1"><input type="checkbox"> Cricket</label>
                            <label class="mx-1"><input type="checkbox"> Volleyball</label>
                            <label class="mx-1"><input type="checkbox"> Chess</label>
                            <label class="mx-1"><input type="checkbox"> Taekwondo</label>
                            <label class="mx-1"><input type="checkbox"> Scrabble</label>
                            <label class="mx-1"><input type="checkbox"> Skating</label>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-8 mt-2">
                      <div class="card-body border rounded h-100">
                        <label class="d-block fw-bold ">Remarks:</label>
                        <textarea class="form-control" rows="8"></textarea>
                        <div class="d-flex justify-content-end mt-3">
                          <div style="border-top: 2px solid #000; width: 200px; display: inline-block;">
                            <p class="text-center fw-bold mb-0">Authority (Signature)</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="btn btn-primary px-4 mx-1" type="submit">Submit</button>
                    <button class="btn btn-danger px-4 mx-1" type="reset">Cancel</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
      <!-- footer -->
      <?php include 'include/footer.php'; ?>
    </div>
  </div>
  <script>
    let stream = null;

    async function startWebcam() {
      const video = document.getElementById('webcam');
      const previewImg = document.getElementById('photoPreview');
      const captureBtn = document.getElementById('captureBtn');
      const startBtn = document.getElementById('startCamBtn');

      try {
        // Browser se camera access mangna
        stream = await navigator.mediaDevices.getUserMedia({
          video: true,
          audio: false
        });
        video.srcObject = stream;

        // UI Change karna
        video.style.display = "block";
        previewImg.style.display = "none";
        captureBtn.style.display = "block";
        startBtn.innerHTML = "<i class='fa fa-sync'></i> Restart Cam";

      } catch (err) {
        alert("Camera access denied or not found!");
        console.error("Error accessing webcam:", err);
      }
    }

    function takeSnapshot() {
      const video = document.getElementById('webcam');
      const canvas = document.getElementById('canvas');
      const previewImg = document.getElementById('photoPreview');
      const captureBtn = document.getElementById('captureBtn');

      // Video se image ko canvas par draw karna
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      canvas.getContext('2d').drawImage(video, 0, 0);

      // Canvas ko image URL mein badalna
      const dataUrl = canvas.toDataURL('image/png');
      previewImg.src = dataUrl;

      // Camera band karna aur UI reset karna
      stopWebcam();
      video.style.display = "none";
      previewImg.style.display = "block";
      captureBtn.style.display = "none";
    }

    function stopWebcam() {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
      }
    }
  </script>
  <!-- General JS Scripts -->
  <script src="assets/js/app.min.js"></script>
  <!-- JS Libraies -->
  <!-- Page Specific JS File -->
  <script src="assets/bundles/datatables/datatables.min.js"></script>
  <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/dataTables.buttons.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.flash.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/jszip.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/pdfmake.min.js"></script>
  <script src="assets/bundles/datatables/export-tables/vfs_fonts.js"></script>
  <script src="assets/bundles/datatables/export-tables/buttons.print.min.js"></script>
  <script src="assets/js/page/datatables.js"></script>
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <!-- Custom JS File -->
  <script src="assets/js/custom.js"></script>
</body>


<!-- forms-validation.html  21 Nov 2019 03:55:16 GMT -->

</html>