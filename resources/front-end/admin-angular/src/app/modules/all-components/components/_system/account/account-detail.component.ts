import {Component, Input, OnInit} from '@angular/core'
import {ControlContainer, FormBuilder, FormGroup, FormGroupDirective, NgModelGroup, Validators} from '@angular/forms'

import {l} from 'src/environments/l'

import {Account} from 'src/app/models/_models'

@Component({
  selector: 'app-account-detail',
  templateUrl: './account-detail.component.html',
  styleUrls: ['./account-detail.component.scss'],
  viewProviders: [{provide: ControlContainer, useExisting: FormGroupDirective}],
})
export class AccountDetailComponent implements OnInit {
  @Input() account!: Account
  mFormGroup!: FormGroup
  labels = l
  mFormGroupName = l.user.accountName.name

  constructor(private parent: FormGroupDirective, private fb: FormBuilder) {}

  ngOnInit(): void {
    this.mFormGroup = this.parent.form

    this.mFormGroup.addControl(
      this.mFormGroupName,
      this.fb.group({
        firstName: [this.account.firstName, Validators.required],
        lastName: [this.account.lastName, Validators.required],
        cellPhone: [this.account.cellPhone],
        birthDate: [this.account.birthDate],
        address: [this.account.address],
        neighborhood: [this.account.neighborhood],
      })
    )
  }
}
