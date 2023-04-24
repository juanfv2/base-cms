import {Component, OnInit, Input} from '@angular/core'
import {FormGroup, FormGroupDirective, FormBuilder, Validators, ControlContainer} from '@angular/forms'

import {k} from 'src/environments/k'
import {l} from 'src/environments/l'

import {Person} from 'src/app/models/_models'

const kRoute = k.routes.people

@Component({
  selector: 'app-person-detail',
  templateUrl: './person-detail.component.html',
  styleUrls: ['./person-detail.component.scss'],
  viewProviders: [{provide: ControlContainer, useExisting: FormGroupDirective}],
})
export class PersonDetailComponent implements OnInit {
  @Input() person!: Person
  mFormGroup!: FormGroup
  labels = l
  mFormGroupName = l.user.personName.name

  constructor(private parent: FormGroupDirective, private fb: FormBuilder) {}

  ngOnInit(): void {
    this.mFormGroup = this.parent.form

    console.log('PersonDetailComponent.person', this.person);

    this.mFormGroup.addControl(
      this.mFormGroupName,
      this.fb.group({
        firstName: [this.person.firstName, Validators.required],
        lastName: [this.person.lastName, Validators.required],
        cellPhone: [this.person.cellPhone],
        birthDate: [this.person.birthDate],
        address: [this.person.address],
        neighborhood: [this.person.neighborhood],
      })
    )
  }
}
