import {Component, OnDestroy, OnInit} from '@angular/core'
import {Title} from '@angular/platform-browser'
import {SwUpdate} from '@angular/service-worker'
import {JfMessageService, JfUtils} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from 'src/environments/k'

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss'],
})
export class AppComponent implements OnInit, OnDestroy {
  inDevMode: any = ''
  message: any
  msgs: any
  mSubscription: any

  constructor(private title: Title, private sWUpdate: SwUpdate, private messageService: JfMessageService) {
    this.inDevMode = JfUtils.mStorage.getItem(k.dev)
    // console.log('this.inDevMode', `"${this.inDevMode}"`)
    this.title.setTitle(`${k.project_name}`)
  }

  ngOnInit(): void {
    this.messageService.currentMessage.subscribe((m: any) => (this.message = m))

    if (this.sWUpdate.isEnabled) {
      this.sWUpdate.versionUpdates.subscribe((next) => {
        window.location.reload()
      })
    }

    // this.messageService.info('AppComponent.ngOnInit()', 'AppComponent.ngOnInit()')
  }

  ngOnDestroy() {
    if (this.mSubscription) {
      this.mSubscription.unsubscribe()
    }
  }

  actClose(): void {
    this.message = {}
  }
}
