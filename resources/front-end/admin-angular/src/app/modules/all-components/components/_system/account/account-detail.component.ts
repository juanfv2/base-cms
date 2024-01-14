import {Component, Input, OnInit} from '@angular/core'
import {ControlContainer, FormBuilder, FormGroup, FormGroupDirective, NgModelGroup, Validators} from '@angular/forms'
import {Account} from '../../../../../models/_models'
import {l} from '../../../../../../environments/l'

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
        first_name: [this.account.first_name, Validators.required],
        last_name: [this.account.last_name, Validators.required],
        cell_phone: [this.account.cell_phone],
        birth_date: [this.account.birth_date],
        address: [this.account.address],
        neighborhood: [this.account.neighborhood],
      })
    )
  }
}
