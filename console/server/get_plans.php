<?php
session_start();

function get_plans(){
    include_once "../../config.php";
    $owner = $_SESSION['teacher_email'];
    $school_uid = $_SESSION['School_uid'];

    $query = mysqli_query($conn,"select * from plans where 
    owner = '$owner' and school_uid = '$school_uid' order by created_at desc");

    while($plan = mysqli_fetch_array($query)){
        $title = $plan["title"];
        $progress = $plan["progress"];
        $desc = $plan["description"];
        $uid = $plan["uid"];
        $model_target = $plan['school_uid'].$uid;

        echo '
            <a href="plan.php?ref='.$uid.'" style="color:inherit" class="plan">
                <!-- <div class="plan-header">
                  <button class="del-btn"  data-bs-toggle="modal" data-bs-target="#'.$model_target.'">
                    <i class="bi bi-trash"></i>
                  </button>
                    <div class="modal fade" id="'.$model_target.'" tabindex="-1">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content card">
                          <div class="modal-header">
                            <h5 class="modal-title">⚠️Alert</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <h2>Are you sure you want to delete this🤷🤷‍♂️?</h2>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn del-btn"><i class="bi bi-trash"> </i> Delete</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  <button class="">
                    <i class="bi bi-pen"></i>
                  </button>
                </div> -->
                <div class="plan-body d-flex justify-between flex-column">
                    <strong><b>'.$title.'</b></strong><br>
                    <pre>'.$desc.'</pre>
                    <div class="progress mt-3">
                        <div class="progress-bar bg-success"  role="progressbar" style="width: '.$progress.'%" aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  
                </div>
            </a>
        
        ';

    }

}
get_plans();