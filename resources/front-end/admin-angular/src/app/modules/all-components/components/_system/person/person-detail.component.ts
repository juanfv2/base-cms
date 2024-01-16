import {Component, OnInit, Input} from '@angular/core'
import {FormGroup, FormGroupDirective, FormBuilder, Validators, ControlContainer} from '@angular/forms'

import {k} from '../../../../../../environments/k'
import {l} from '../../../../../../environments/l'

import {Person} from '../../../../../models/_models'

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
  mFormGroupName = l.user.person.name

  constructor(private parent: FormGroupDirective, private fb: FormBuilder) {}

  ngOnInit(): void {
    this.mFormGroup = this.parent.form

    console.log('PersonDetailComponent.person', this.person)

    this.mFormGroup.addControl(
      this.mFormGroupName,
      this.fb.group({
        first_name: [this.person.first_name, Validators.required],
        last_name: [this.person.last_name, Validators.required],
        cell_phone: [this.person.cell_phone],
        birth_date: [this.person.birth_date],
        address: [this.person.address],
        neighborhood: [this.person.neighborhood],
      })
    )
  }
}
