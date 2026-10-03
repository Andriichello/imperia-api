import type {EditorDashboard} from '@/api'

/** A restaurant the admin can edit (see `RestaurantEditorRepository::editableBy()`). */
export interface AdminRestaurant {
  id: number
  slug: string | null
  name: string
  default_locale: string
}

/** Pages of the admin (see `AdminPageController::signedIn()`). */
export interface AdminUrls {
  dashboard: string
  editor: string
  logout: string
  // planned menu changes: all versions of the restaurant
  versions: string
  // of a version: this one and `/{id}`
  version: string
}

export interface AdminUser {
  id: number
  name: string
  email: string
}

/** Props of the dashboard (see `DashboardController`). */
export interface DashboardProps {
  locale: string
  user: AdminUser
  restaurants: AdminRestaurant[]
  urls: AdminUrls
  restaurant: EditorDashboard
}

/** Props of planned menu changes: the dashboard's ones, with all versions (see `VersionPageController::index()`). */
export type VersionsProps = DashboardProps
