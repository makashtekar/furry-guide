<?php 

require( __DIR__ . '/vendor/autoload.php' );

$conn = new MongoDB\Client( 'mongodb://localhost:27017' );
$testDb = $conn->saurabh_test;
$Collection = $conn->saurabh_test->BioData ;

$action = $argv[1];
$namedArgs = [];


for( $i = 2 ; $i < count( $argv ); $i++ ){
    $exp = explode( '=', $argv[ $i ] );
    $namedArgs[ $exp[0] ] = $exp[1];
}

    if( $action == "--save" ){

        $User_Id_str = ( int ) $namedArgs['user_id'] ;
        $User_title_str = $namedArgs['title'] ; 
        
       InsertData( $User_Id_str , $User_title_str ) ;

    }
    else if( $action == "--update" )
    {
        
        $Id_str = ( int ) $namedArgs[ 'id' ] ;
        
        $Title_str = null;
        $isCompleted = null;

        if( isset( $namedArgs['title'] ) ){ //Title
            $Title_str = $namedArgs['title']; 
        }

        if( isset( $namedArgs['completed'] ) ){ //isCompleted
            $isCompleted = $namedArgs['completed']; 
            $isCompleted = $isCompleted == '1';
        }
        
        UpdateData( $Id_str , $Title_str  ,$isCompleted ) ; 

    }else if( $action == "--delete" ){

        $recId = (int) $namedArgs['id'];
        DeleteData( $recId ) ;

    }else if( $action == "--list")  {
         
        $User_Id_str = (int) $namedArgs['user_id'];
        $isCompleted = ( int )$namedArgs[ 'completed' ] ;
        

        if( $namedArgs['completed'] == "0" ){ // Checking the agrument
             
            ShowRecords( $User_Id_str , false) ;    
        }else{
            ShowRecords( $User_Id_str , true) ;    

        }
        

    }
    
function InsertData( $UserId , $title ){

 global $Collection ;
 
 $Result = $Collection->insertOne( ["userId" => $UserId , "title" => $title , "id" => getLastId() ]  ) ;

     if( $Result->getInsertedCount() > 0 ){
        print_r( $Result ) ;
        echo "Data Inserted Successfully" ;
     }  ;
    
 }
 //InsertData(  ) ;

function UpdateData( $Id , $title , $isCompleted ){

    global $Collection ;
    global $Records ;

    $updateWith = [ ];

    if(  $title !== null ){
        $updateWith['title']  = $title;
    } 

    if( $isCompleted !== null ){
        $updateWith['completed'] = $isCompleted;
    }

    $Result = $Collection->updateOne( 

        [ 'id' => $Id ] , 
        ['$set' => $updateWith ]
       ) ;
     if( $Result  ){

        echo "Data Updated Successfully" ; 
     } 
 }
    //UpdateData() ;

function DeleteData( $Id ){

    global $Collection ;
    global $Records ;

     $deleteResult = $Collection->deleteOne(['id' => $Id]);

     if( $deleteResult->getDeletedCount() > 0 ){

        echo "Record Deleted Successfully" ;
     }
     
 }
    //DeleteData() ;

function ShowRecords( $userId , $isCompleted ){

    global $Collection ; 
    
    $Show_rec =  $Collection->find( ["userId" => $userId , "completed" => $isCompleted ] );
      // print_r( $Show_rec ) ;
      foreach( $Show_rec as $Rec ){

       // var_dump( $Rec->userId  . "\t" . $Rec->title . "\t" . $Rec->completed . "\n" ); 
        var_dump( $Rec->id );
    }
 }   
    //ShowRecords() ;
function GetFakeTodos(){

    
    global $Collection ; 
    
    $Fake_Todos = json_decode( file_get_contents( 'https://jsonplaceholder.typicode.com/todos' ) );

    $Collection->insertMany( $Fake_Todos ) ;

      echo "Records Added Succfully" ;
 }
 //GetFakeTodos() ;

function Check_IncompleteTodo(){

   global $Collection ;
     $User = $Collection->find(  ["userId" => 1  , "completed" => false]  );
     foreach( $User as $Rec ){
     //var_dump( $Rec->completed ) ;
     echo $Rec->userId . "\t" . $Rec->id . "\t" . $Rec->title . "\t" . $Rec->completed . "\n" ;
     }
 }
  //Check_IncompleteTodo() ;
 
function GetCompletedTodo(){
    global $Collection ; 
    $User = $Collection->find(  ["userId" => 1  , "completed" => true]  );
    foreach( $User as $Rec ){
        
        echo $Rec->userId . "\t" . $Rec->id . "\t" . $Rec->title . "\t" . $Rec->completed . "\n" ;
        }
    }
     //GetCompleteTodo() ;


function GetDynamicRes( $UserId  , $Status){

    global $Collection ;

    $User = $Collection->find(  [ "userId" => $UserId  , "completed" => $Status]  );

    foreach( $User as $Rec ){
        
        echo $Rec->userId . "\t" . $Rec->id . "\t" . $Rec->title . "\t" . $Rec->completed . "\n" ;

    }


}
  //GetDynamicRes( 2 , false) ;
function getLastId( ){  // function to get the last record id

    global $Collection ;
    $options = [ 'sort' => [ 'id' => -1 ] , 'limit' => 1  ];
    $results = $Collection->find([], $options ) ;
    $lastId = 0;
    
    foreach( $results as $result ){ $lastId = $result->id; break; }
    return $lastId + 1 ;
  }
    
 



