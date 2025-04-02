from fastapi import FastAPI, HTTPException, Depends
from sqlalchemy import Column, Integer, String, DateTime, Float, Text, create_engine, ForeignKey
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker, Session, relationship
from pydantic import BaseModel
from datetime import datetime
from typing import Optional
from fastapi.middleware.cors import CORSMiddleware



# Database Configuration
DATABASE_URL = "mysql+mysqlconnector://root:admin123@localhost/demo_ioncudos_31jan25"

# Create the database engine
engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base = declarative_base()

app = FastAPI()
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://192.168.114.165:3000"],  # Replace * with the frontend's actual IP if needed
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# Curriculum Table
class Curriculum(Base):
    __tablename__ = "curriculum"
    crclm_id = Column(Integer, primary_key=True, index=True)
    courses = relationship("Course", back_populates="curriculum")

# CourseDomain Table
class CourseDomain(Base):
    __tablename__ = "course_domain"
    crs_domain_id = Column(Integer, primary_key=True, index=True, autoincrement=True)
    courses = relationship("Course", back_populates="course_domain")
# CourseType Table
class CourseType(Base):
    __tablename__ = "course_type"
    crs_type_id = Column(Integer, primary_key=True, index=True, autoincrement=True)
    courses = relationship("Course", back_populates="course_type")


# Course Table (Retaining All Columns & Fixing Relationships)
class Course(Base):
    __tablename__ = "course"
    crs_id = Column(Integer, primary_key=True, index=True, autoincrement=True)
    crs_mode = Column(Integer)
    crs_code = Column(String(30))
    crs_title = Column(String(100))
    crs_acronym = Column(String(30))
    co_crs_owner = Column(String(1000))
    lect_credits = Column(Float)
    tutorial_credits = Column(Float)
    practical_credits = Column(Float)
    self_study_credits = Column(Float)
    total_credits = Column(Float)
    cia_flag = Column(Integer)
    mte_flag = Column(Integer)
    tee_flag = Column(Integer)
    total_cia_weightage = Column(Float)
    total_mte_weightage = Column(Float)
    total_tee_weightage = Column(Float)
    cia_passing_marks = Column(Float)
    tee_passing_marks = Column(Float)
    contact_hours = Column(Float)
    cie_marks = Column(Float)
    mid_term_marks = Column(Float)
    see_marks = Column(Float)
    attendance_marks = Column(Float)
    ss_marks = Column(Float)
    total_marks = Column(Float)
    see_duration = Column(String(4))
    cognitive_domain_flag = Column(Integer)
    affective_domain_flag = Column(Integer)
    psychomotor_domain_flag = Column(Integer)
    created_by = Column(Integer)
    modified_by = Column(Integer)
    create_date = Column(DateTime, default=datetime.utcnow)
    modify_date = Column(DateTime, default=datetime.utcnow)
    state_id = Column(Integer)
    topic_publish_flag = Column(Integer)
    status = Column(Integer)
    target_status = Column(Integer)
    target_comment = Column(Text)
    cia_course_minthreshhold = Column(Integer)
    mte_course_minthreshhold = Column(Integer)
    tee_course_minthreshhold = Column(Integer)
    course_studentthreshhold = Column(Integer)
    justify = Column(String(1000))
    clo_bl_flag = Column(Integer)
    edu_sys_flag = Column(Integer)
    total_stud_enroll = Column(Integer)
    reg_start_date = Column(DateTime)
    reg_end_date = Column(DateTime)
    crs_attainment_finalize_flag = Column(Integer)
    crs_mte_finalize_flag = Column(Integer)
    import_ref_crs_id = Column(Integer)
    elective_crs_flag = Column(Integer)
    co_attainment_observation = Column(Text)
    co_attainment_action_plan = Column(Text)
    co_attainment_hod_remarks = Column(Text)
    crs_bl_sugg_flag = Column(Integer)
    cia_course_target = Column(Integer)
    mte_course_target = Column(Integer)
    tee_course_target = Column(Integer)
    tutorial = Column(Integer)
    indirect_flag = Column(Integer)
    direct_percentage = Column(Integer)
    indirect_percentage = Column(Integer)
    avg_flag = Column(Integer)
    lms_topic_import_type_flag = Column(Integer)
    finalize_and_ready_for_ems_flag = Column(Integer)
    crclm_term_id = Column(Integer, nullable=True)
    
    # Foreign Keys
    crclm_id = Column(Integer, ForeignKey("curriculum.crclm_id"), nullable=True)
    crs_domain_id = Column(Integer, ForeignKey("course_domain.crs_domain_id"), nullable=True)
    crs_type_id = Column(Integer, ForeignKey("course_type.crs_type_id"), nullable=True)

    # Relationships (Fixed)
    curriculum = relationship("Curriculum", back_populates="courses")
    course_domain = relationship("CourseDomain", back_populates="courses")
    course_type = relationship("CourseType", back_populates="courses")

# Create database tables
Base.metadata.create_all(bind=engine)

# Dependency to get database session
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

# Pydantic Model (For API Requests)
class CourseCreate(BaseModel):
    crs_mode: int
    crs_code: str
    crs_title: str
    crs_acronym: str
    co_crs_owner: str
    lect_credits: float
    tutorial_credits: float
    practical_credits: float
    self_study_credits: float
    total_credits: float
    cia_flag: int
    mte_flag: int
    tee_flag: Optional[int] = None
    total_cia_weightage: Optional[float] = None
    total_mte_weightage: Optional[float] = None
    total_tee_weightage: Optional[float] = None
    cia_passing_marks: Optional[float] = None
    tee_passing_marks: Optional[float] = None
    contact_hours: Optional[float] = None
    cie_marks: Optional[float] = None
    mid_term_marks: Optional[float] = None
    see_marks: Optional[float] = None
    attendance_marks: Optional[float] = None
    ss_marks: Optional[float] = None
    total_marks: Optional[float] = None
    see_duration: Optional[str] = None
    cognitive_domain_flag: Optional[int] = None
    affective_domain_flag: Optional[int] = None
    psychomotor_domain_flag: Optional[int] = None
    created_by: Optional[int] = None
    modified_by: Optional[int] = None
    state_id: Optional[int] = None
    topic_publish_flag: Optional[int] = None
    status: Optional[int] = None
    target_status: Optional[int] = None
    target_comment: Optional[str] = None
    cia_course_minthreshhold: Optional[int] = None
    mte_course_minthreshhold: Optional[int] = None
    tee_course_minthreshhold: Optional[int] = None
    course_studentthreshhold: Optional[int] = None
    justify: Optional[str] = None
    clo_bl_flag: Optional[int] = None
    edu_sys_flag: Optional[int] = None
    total_stud_enroll: Optional[int] = None
    reg_start_date: Optional[datetime] = None
    reg_end_date: Optional[datetime] = None
    crs_attainment_finalize_flag: Optional[int] = None
    crs_mte_finalize_flag: Optional[int] = None
    import_ref_crs_id: Optional[int] = None
    elective_crs_flag: Optional[int] = None
    co_attainment_observation: Optional[str] = None
    co_attainment_action_plan: Optional[str] = None
    co_attainment_hod_remarks: Optional[str] = None
    crs_bl_sugg_flag: Optional[int] = None
    cia_course_target: Optional[int] = None
    mte_course_target: Optional[int] = None
    tee_course_target: Optional[int] = None
    tutorial: Optional[int] = None
    indirect_flag: Optional[int] = None
    direct_percentage: Optional[int] = None
    indirect_percentage: Optional[int] = None
    avg_flag: Optional[int] = None
    lms_topic_import_type_flag: Optional[int] = None
    finalize_and_ready_for_ems_flag: Optional[int] = None
    crclm_id: Optional[int] = None
    crs_domain_id: Optional[int] = None
    crclm_term_id: Optional[int] = None
    crs_type_id: Optional[int] = None 

    

# CRUD Operations
@app.post("/courses/")
def create_course(course: CourseCreate, db: Session = Depends(get_db)):
    try:
        new_course = Course(
            **course.dict(),
            create_date=datetime.utcnow(),
            modify_date=datetime.utcnow()
        )
        db.add(new_course)
        db.commit()
        db.refresh(new_course)
        return {"message": "Course added successfully", "course": new_course}
    except Exception as e:
        db.rollback()
        raise HTTPException(status_code=500, detail=f"Error creating course: {str(e)}")

@app.get("/courses/")
def get_courses(db: Session = Depends(get_db)):
    return db.query(Course).all()

@app.get("/courses/{crs_id}")
def get_course(crs_id: int, db: Session = Depends(get_db)):
    course = db.query(Course).filter(Course.crs_id == crs_id).first()
    if not course:
        raise HTTPException(status_code=404, detail="Course not found")
    return course

@app.put("/courses/{crs_id}")
def update_course(crs_id: int, updated_course: CourseCreate, db: Session = Depends(get_db)):
    course = db.query(Course).filter(Course.crs_id == crs_id).first()
    if not course:
        raise HTTPException(status_code=404, detail="Course not found")
    
    updated_data = updated_course.dict(exclude_unset=True)
    for key, value in updated_data.items():
        setattr(course, key, value)
    
    course.modify_date = datetime.utcnow()
    db.commit()
    db.refresh(course)
    return {"message": "Course updated successfully", "course": course}

@app.delete("/courses/{crs_id}")
def delete_course(crs_id: int, db: Session = Depends(get_db)):
    course = db.query(Course).filter(Course.crs_id == crs_id).first()
    if not course:
        raise HTTPException(status_code=404, detail="Course not found")
    
    db.delete(course)
    db.commit()
    return {"message": "Course deleted successfully"}
