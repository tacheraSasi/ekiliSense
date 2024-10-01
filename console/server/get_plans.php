<?php
session_start();

function get_plans(){
    include_once "../../config.php";
    include_once "../functions/timeAgo.php";
    $owner = $_SESSION['teacher_email'];
    $school_uid = $_SESSION['School_uid'];
    

    $query = mysqli_query($conn,"select * from plans where 
    owner = '$owner' and school_uid = '$school_uid' order by created_at desc");

    while($plan = mysqli_fetch_array($query)){
        $title = $plan["title"];
        $progress = $plan["progress"];
        $desc = $plan["description"];
        $uid = $plan["uid"];
        $timeAgo = timeAgo(strtotime($plan['created_at']));
        $status = "pending";

        #status
        if($progress > 80){
          $status = "almost_done";
        }else if ($progress > 20 && $progress < 40){
          $status = "not_moving";
        }else if ($progress > 40 && $progress < 60){
          $status = "pending";
        }else if ($progress >= 60){
          $status = "in_progress";
        }else{
          $status = "started";
        }

        #bg-colors
        if($status == "almost_done"){
          $bg_color = "bg-success";
        }else if ($status == "not_moving"){
          $bg_color = "bg-warning";
        }else if ($status == "pending"){
          $bg_color = "bg-info";
        }else if ($status == "in_progress"){
          $bg_color = "bg-secondary";
        }else{
          $bg_color = "bg-primary";
        }

        #text color
        $textColor = $bg_color == "bg-info" || $bg_color == "bg-warning" ? "text-dark":""; 


        echo '
            <a href="plan.php?ref='.$uid.'" style="color:inherit" class="plan">
                
                <div class="plan-body d-flex justify-between flex-column">
                    <div class="plan-top d-flex justify-between">
                      <span class="badge rounded-pill '.$bg_color.' '.$textColor.' ">'.$status.'</span>
                      <span class="flex-end timeago">'.$timeAgo.'</span>
                    </div>
                    <strong><b>'.$title.'</b></strong><br>
                    <pre>'.$desc.'</pre>
                    <div class="progress mt-3">
                        <div class="progress-bar '.$bg_color.'"  role="progressbar" style="width: '.$progress.'%" aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100">'.$progress.'%</div>
                    </div>
                  
                </div>
            </a>
        
        ';

    }

}
get_plans();