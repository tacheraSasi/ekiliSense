package main

import (
	"fmt"
	"math/rand"
	"time"
	
	"github.com/xuri/excelize/v2"
)

type Teacher struct {
	Name  string
	Email string
	Phone string
}

func GenerateDummyTeachers(count int) []Teacher {
	rand.Seed(time.Now().UnixNano())
	
	firstNames := []string{"John", "Emma", "Michael", "Sophia", "William", "Olivia", "James", "Ava", "Robert", "Mia"}
	lastNames := []string{"Smith", "Johnson", "Brown", "Taylor", "Miller", "Wilson", "Davis", "Anderson", "Thomas", "Moore"}
	domains := []string{"school.edu", "college.ac", "university.org"}

	teachers := make([]Teacher, count)
	for i := 0; i < count; i++ {
		firstName := firstNames[rand.Intn(len(firstNames))]
		lastName := lastNames[rand.Intn(len(lastNames))]
		email := fmt.Sprintf("%s.%s%d@%s", 
			firstName, 
			lastName, 
			rand.Intn(90)+10, 
			domains[rand.Intn(len(domains))],
		)
		
		teachers[i] = Teacher{
			Name:  fmt.Sprintf("%s %s", firstName, lastName),
			Email: email,
			Phone: fmt.Sprintf("+1-%03d-%03d-%04d", 
				rand.Intn(999), 
				rand.Intn(999), 
				rand.Intn(9999),
			),
		}
	}
	return teachers
}

func CreateTeachersExcel(filename string) error {
	
	f := excelize.NewFile()
	defer f.Close()
	
	// Creates new sheet
	index, err := f.NewSheet("Teachers")
	if err != nil {
		return fmt.Errorf("error creating sheet: %v", err)
	}

	// Set headers
	headers := map[string]string{
		"A1": "Name",
		"B1": "Email",
		"C1": "Phone",
	}
	
	// Style for headers
	style, err := f.NewStyle(&excelize.Style{
		Font: &excelize.Font{Bold: true},
	})
	if err != nil {
		return fmt.Errorf("error creating style: %v", err)
	}

	for cell, value := range headers {
		f.SetCellValue("Teachers", cell, value)
		f.SetCellStyle("Teachers", cell, cell, style)
	}

	// Generate dummy data
	teachers := GenerateDummyTeachers(10) 

	// Populate data
	for i, teacher := range teachers {
		row := i + 2 // Start from row 2
		f.SetCellValue("Teachers", fmt.Sprintf("A%d", row), teacher.Name)
		f.SetCellValue("Teachers", fmt.Sprintf("B%d", row), teacher.Email)
		f.SetCellValue("Teachers", fmt.Sprintf("C%d", row), teacher.Phone)
	}

	// Set active sheet and save
	f.SetActiveSheet(index)
	if err := f.SaveAs(filename); err != nil {
		return fmt.Errorf("error saving file: %v", err)
	}

	return nil
}

func main() {
	err := CreateTeachersExcel("teachers.xlsx")
	if err != nil {
		fmt.Printf("Error creating Excel file: %v\n", err)
		return
	}
	fmt.Println("Excel file created successfully!")
}