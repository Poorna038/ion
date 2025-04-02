from fastapi import FastAPI, HTTPException, Depends
from sqlalchemy import Column, Integer, String, Date, ForeignKey, create_engine
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker, Session, relationship
from pydantic import BaseModel
from datetime import date
from typing import List, Optional

# Database Configuration
DATABASE_URL = "mysql+mysqlconnector://root:admin123@localhost/demo_ioncudos_31jan25"

# Create the database engine
engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base = declarative_base()

app = FastAPI()

# Curriculum Table Model (corrected table name and column name for foreign key)
class Curriculum(Base):
    __tablename__ = "curriculum"
    crclm_id = Column(Integer, primary_key=True, index=True)
    
# Course Table Model (for the 'course' table with 'crs_id' as primary key)
class Course(Base):
    __tablename__ = "course"
    crs_id = Column(Integer, primary_key=True, index=True)
    

# CLO Table Model (foreign key reference to curriculum)
class CLO(Base):
    __tablename__ = "clo"
    clo_id = Column(Integer, primary_key=True, index=True, autoincrement=True)
    clo_statement = Column(String(2000))
    clo_code = Column(String(10))
    crclm_id = Column(Integer, ForeignKey("curriculum.crclm_id"))  # ForeignKey referencing curriculum.id
    crs_id = Column(Integer, ForeignKey("course.crs_id"))
    created_by = Column(Integer, nullable=True)  # Allow created_by to be NULL
    modified_by = Column(Integer, nullable=True)  # Allow modified_by to be NULL
    term_id = Column(Integer, nullable=True)
    create_date = Column(Date, default=date.today)
    modify_date = Column(Date, default=date.today)
    cia_clo_minthreshhold = Column(Integer)
    mte_clo_minthreshhold = Column(Integer)
    tee_clo_minthreshhold = Column(Integer)
    clo_studentthreshhold = Column(Integer)
    justify = Column(String(1000), nullable=True)  # Allow justify to be NULL

    # Relationship to Curriculum (for easier access to related curriculum data)
    curriculum = relationship("Curriculum", backref="clos")
    course = relationship("Course", backref="clos")

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
class CLOCreate(BaseModel):
    clo_statement: str
    clo_code: str
    crclm_id: Optional[int] = None  # Optional field for the foreign key
    crs_id: Optional[int] = None 
    term_id: Optional[int] = None 
    create_date: Optional[date] = None
    modify_date: Optional[date] = None
    cia_clo_minthreshhold: int
    mte_clo_minthreshhold: int
    tee_clo_minthreshhold: int
    clo_studentthreshhold: int
    justify: Optional[str] = None  # Make justify optional

# CRUD Operations

@app.post("/clo/")
def create_clo(clo: CLOCreate, db: Session = Depends(get_db)):
    # Ensure crclm_id exists in Curriculum table if provided
    if clo.crclm_id is not None:
        curriculum = db.query(Curriculum).filter(Curriculum.crclm_id == clo.crclm_id).first()
        if not curriculum:
            raise HTTPException(status_code=400, detail="Invalid crclm_id. The referenced curriculum does not exist.")

    # Ensure crs_id exists in Course table if provided
    if clo.crs_id is not None:
        course = db.query(Course).filter(Course.crs_id == clo.crs_id).first()
        if not course:
            raise HTTPException(status_code=400, detail="Invalid crs_id. The referenced course does not exist.")
    
    try:
        new_clo = CLO(
            clo_statement=clo.clo_statement,
            clo_code=clo.clo_code,
            crclm_id=clo.crclm_id,  # Will be NULL if not provided
            crs_id=clo.crs_id,  # Will be NULL if not provided
            term_id=clo.term_id,
            created_by=None,  # Set created_by as NULL
            modified_by=None,  # Set modified_by as NULL
            create_date=clo.create_date or None,
            modify_date=clo.modify_date or None,
            cia_clo_minthreshhold=clo.cia_clo_minthreshhold,
            mte_clo_minthreshhold=clo.mte_clo_minthreshhold,
            tee_clo_minthreshhold=clo.tee_clo_minthreshhold,
            clo_studentthreshhold=clo.clo_studentthreshhold,
            justify=clo.justify or None
        )
        db.add(new_clo)
        db.commit()
        db.refresh(new_clo)
        return {"message": "CLO added successfully", "clo": new_clo}
    except Exception as e:
        db.rollback()
        raise HTTPException(status_code=400, detail=f"Error: {str(e)}")


@app.get("/clo/") 
def get_clos(db: Session = Depends(get_db)):
    return db.query(CLO).all()

@app.get("/clo/{clo_id}")
def get_clo(clo_id: int, db: Session = Depends(get_db)):
    clo = db.query(CLO).filter(CLO.clo_id == clo_id).first()
    if not clo:
        raise HTTPException(status_code=404, detail="CLO not found")
    return clo

@app.put("/clo/{clo_id}")
def update_clo(clo_id: int, updated_clo: CLOCreate, db: Session = Depends(get_db)):
    try:
        clo = db.query(CLO).filter(CLO.clo_id == clo_id).first()
        if not clo:
            raise HTTPException(status_code=404, detail="CLO not found")
        
        updated_data = updated_clo.dict(exclude_unset=True)
        for key, value in updated_data.items():
            setattr(clo, key, value)
        
        clo.modify_date = date.today()
        db.commit()
        db.refresh(clo)
        return {"message": "CLO updated successfully", "clo": clo}
    except Exception as e:
        db.rollback()
        raise HTTPException(status_code=400, detail=f"Error: {str(e)}")

@app.delete("/clo/{clo_id}")
def delete_clo(clo_id: int, db: Session = Depends(get_db)):
    try:
        clo = db.query(CLO).filter(CLO.clo_id == clo_id).first()
        if not clo:
            raise HTTPException(status_code=404, detail="CLO not found")
        
        db.delete(clo)
        db.commit()
        return {"message": "CLO deleted successfully"}
    except Exception as e:
        db.rollback()
        raise HTTPException(status_code=400, detail=f"Error: {str(e)}")
