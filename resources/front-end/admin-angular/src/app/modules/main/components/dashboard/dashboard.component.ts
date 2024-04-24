import {Component} from '@angular/core'
import {JfUtils} from 'base-cms'
import {k} from '../../../../../environments/k'
import {l} from '../../../../../environments/l'

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss'],
})
export class DashboardComponent {
  labels = l
  inDevMode = ''
  data: any = {}

  constructor() {
    this.inDevMode = `${JfUtils.mStorage.getItem(k.dev)}`
  }
}
