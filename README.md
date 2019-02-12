    # CodeIgniter-3.X-Extension-
    Related extensions to CodeIgniter 3.x Framework are available here
    
    Student.php
    
    <?php
    
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Student extends \MY_Model{
    	
    	public $tableName = "student";
    	public $id,$name,$age;
    	
    	public function getCard(){
    		return $this->hasOne(\Card::class,"id","studentId");
    	}
    	
    	public function getBooks(){
    		return $this->hasMany(\Book::class,"id","studentId");
    	}
    	
    	public function getTeachers(){
    		return $this->belongsToMany("studentteacher", \Teacher::class, "id", "studentId", "id", "teacherId");
    	}
    }
    
    Book.php
    
    <?php
    
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Book extends \MY_Model {
    	public $tableName = "book";
    	public $id,$studentId,$name,$price;
    	
    	public function getStudent(){
    		return $this->belongsTo(\Student::class,"id","studentId");
    	}
    }
    
    
    
    In your controller:
    
    public function index()
    {		
    	$this->load->model("Student");
    	$student = $this->Student;
    
    	var_dump($student->getCount());
    }
    
